<?php /** @noinspection LaravelFunctionsInspection */

namespace App\Commands;

use App\Services\TerminalService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Process\Process;

class Release extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'release {type : major|minor|patch} {--message=} {--force}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Release a new version on github';
    protected ?string $github_token;
    protected ?string $github_repository;

    protected string $old_tag;
    protected string $old_version;
    protected string $new_version;
    protected string $new_tag;

    protected string|array|null $type;

    protected string $changes;

    protected string $release_url;

    public function __construct(
        protected TerminalService $terminal
    ) {
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * @param  TerminalService  $terminal
     *
     * @return mixed
     */
    public function handle(): int
    {
        $this->terminal->init($this->output);

        $this->type = $this->argument('type');

        $this->github_token = env('GITHUB_TOKEN');

        if (empty($this->github_token)) {
            $this->error('GITHUB_TOKEN .env variable is required');
            return self::FAILURE;
        }

        if (!in_array($this->type, ['major', 'minor', 'patch'])) {
            $this->error('Invalid release type. Valid types are: major, minor, patch');
            return self::FAILURE;
        }

        $success =
            $this->check_uncommitted_changes()
            && $this->get_repository()
            && $this->get_current_version()
            && $this->bump_new_version()
            && $this->create_new_tag()
            && $this->get_changes()
            && $this->release();

        if (!$success) {
            return self::FAILURE;
        }

        $this->info("✅ Created GitHub release: $this->release_url");

        return self::SUCCESS;
    }

    public function get_current_version(): bool
    {
        return $this->task('Fetching latest tag', function() {
            $response = Http::withToken($this->github_token)
                ->get("https://api.github.com/repos/$this->github_repository/releases/latest");

            if ($response->failed()) {
                $this->error('Failed to fetch latest tag');

                dump($response->status(), $response->json());
                return false;
            }

            $this->old_tag = $response->json('tag_name');
            $this->old_version = ltrim($response->json('tag_name'), 'v');

            return true;
        });
    }

    public function bump_new_version(): bool
    {
        return $this->task("Bumping new $this->type version from $this->old_tag", function() {

            [$major, $minor, $patch] = array_pad(explode('.', $this->old_version), 3, 0);

            switch ($this->type) {
                case 'major':
                    $major++;
                    $minor = $patch = 0;
                    break;
                case 'minor':
                    $minor++;
                    $patch = 0;
                    break;
                case 'patch':
                    $patch++;
                    break;
            }

            $this->new_version = "$major.$minor.$patch";

            if (str_starts_with($this->old_tag, 'v')) {
                $this->new_tag = "v$this->new_version";
            } else {
                $this->new_tag = $this->new_version;
            }

            return true;
        });
    }

    public function create_new_tag(): bool
    {
        return $this->task("Creating new tag $this->new_tag", function() {

            if (!$this->option('force') && !$this->confirm("Are you sure you want to create a new tag $this->new_tag?")) {
                $this->warn('Aborted');
                return false;
            }

            return $this->terminal->execute_in_shell_command_line([
                    'cd src',
                    '&&',
                    'git', 'tag', $this->new_tag,
                    '&&',
                    'git', 'push',
                    '&&',
                    'git', 'push', '--tags',
                ]) === self::SUCCESS;
        });
    }

    public function get_repository(): bool
    {
        return $this->task("Detecting repository", function() {
            $process = Process::fromShellCommandline(implode(' ', [
                'cd src',
                '&&',
                'git', 'config', '--get', 'remote.origin.url',
            ]));

            $process->run();

            if (!$process->isSuccessful()) {
                $this->error('Failed to detect repository');

                return false;
            }


            $url = trim($process->getOutput());

            $this->github_repository = (new Stringable($url))->afterLast('github.com:')->beforeLast('.git')->__toString();

            return true;
        });
    }

    public function get_changes(): bool
    {
        return $this->task("Detecting changes", function() {
            $process = Process::fromShellCommandline(implode(' ', [
                'cd src',
                '&&',
                'git', 'log', "$this->old_tag..HEAD", '--pretty="format: - %s[####]%an"',
            ]));

            $process->run();

            if (!$process->isSuccessful()) {
                $this->changes = "No commits found since $this->old_tag";

                return true;
            }


            $output = trim($process->getOutput());

            $output = new Stringable($output);

            $changes = $output->isEmpty()
                ? 'No commits found'
                : $output->explode("\n")
                    ->map(function($line) {
                        $line = trim($line);
                        $line = ltrim($line, '- ');

                        [$message, $author] = explode('[####]', $line);

                        $message = new Stringable($message);

                        if ($author === 'dependabot[bot]') {
                            $author = 'dependabot';
                        }

                        $type = match (true) {
                            $message->contains('Merge pull request') && $message->contains('/dependabot/') => 'dependencies_pull_request',
                            $message->contains('Merge pull request') => 'pull_request',
                            $author === 'dependabot' => 'dependencies',
                            $message->startsWith('[fix]') => 'fix',
                            $message->startsWith('fix') => 'fix',
                            $message->startsWith('[feat]') => 'feat',
                            $message->startsWith('[chore]') => 'chore',
                            $message->startsWith('[docs]') => 'docs',
                            $message->startsWith('[style]') => 'style',
                            $message->startsWith('[refactor]') => 'refactor',
                            $message->startsWith('[perf]') => 'perf',
                            $message->startsWith('[test]') => 'test',
                            $message->startsWith('[ci]') => 'ci',
                            $message->startsWith('[build]') => 'build',
                            $message->startsWith('[revert]') => 'revert',
                            $message->contains('wip') => 'wip',
                            $message->contains('bump') => 'bump',
                            default => 'other',
                        };

                        if ($type === 'pull_request' || $type === 'dependencies_pull_request') {
                            $pull_request_number = (int) $message->after('Merge pull request #')->before(' from')->__toString();
                        }

                        $priority = match ($type) {
                            'pull_request' => 1,
                            'other' => 99,
                            'dependencies_pull_request' => 100,
                            default => 1000,
                        };

                        return [
                            'type' => $type,
                            'priority' => $priority,
                            'author' => $author,
                            'message' => (string) $message,
                            'pull_request_number' => $pull_request_number ?? null,
                        ];
                    })
                    ->sortBy('priority')
                    ->groupBy('type')
                    ->except(['bump', 'dependencies'])
                    ->map(function(Collection $changes, $group) {
                        $group = Str::of($group)->headline();
                        return "- **$group:**\n".$changes
                                ->map(fn($change) => Str::of("  - ")
                                    ->append($change['message'])
                                    ->append(' by ', "*".$change['author']."*")
                                    ->when($change['pull_request_number'], fn($message) => $message->append(' (', "#{$change['pull_request_number']}", ')'))
                                )->implode("\n")."\n";

                    });

            $this->changes = <<<EOF
## What's Changed

$output

**Full Changelog**: https://github.com/$this->github_repository/compare/$this->old_tag...$this->new_tag
EOF;

            return true;
        });
    }

    public function check_uncommitted_changes(): bool
    {
        $process = Process::fromShellCommandline(implode(' ', [
            'cd src',
            '&&',
            'git', 'status', '--porcelain',
        ]));

        $process->run();

        if (!$process->isSuccessful()) {
            $this->error("Failed to check for uncommitted changes");
            return false;
        }

        if (trim($process->getOutput()) !== '') {
            $this->warn("⚠️ There are uncommitted changes in your working directory.");

            if (!$this->confirm('Continue anyway?')) {
                $this->info('Aborted.');
                return false;
            }
        }

        return true;

    }

    public function release(): bool
    {
        return $this->task("Creating release $this->new_tag", function() {
            $response = Http::withToken($this->github_token)
                ->post("https://api.github.com/repos/$this->github_repository/releases", [
                    'tag_name' => $this->new_tag,
                    'name' => $this->new_tag,
                    'body' => $this->changes,
                    'draft' => false,
                    'prerelease' => false,
                ]);

            if ($response->failed()) {
                $this->error('Failed to create GitHub release');

                dump($response->status(), $response->json());
                return false;
            }

            $this->release_url = $response->json('html_url');

            return true;
        });
    }
}
