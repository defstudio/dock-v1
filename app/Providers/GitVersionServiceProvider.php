<?php


	namespace App\Providers;


	use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Support\ServiceProvider;
    use Symfony\Component\Process\Process;

    class GitVersionServiceProvider extends ServiceProvider{
        /**
         * {@inheritdoc}
         */
        public function boot(): void
        {
            $this->app->bind(
                'git.version',
                function (Application $app) {
                    $lastRevisionTag = '$(git rev-list --tags --max-count=1)';

                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' || strtoupper(PHP_OS) === 'LINUX') {
                        $taskGetLastRevisionTag = ['git', 'rev-list', '--tags', '--max-count=1'];

                        $process = tap(new Process($taskGetLastRevisionTag, $app->basePath()))->run();

                        $lastRevisionTag = trim($process->getOutput()) ?: 'unreleased';

                        if ($lastRevisionTag === 'unreleased') {
                            return 'unreleased';
                        }
                    }
                    $task = ['git', 'describe', '--tags', $lastRevisionTag];

                    $process = tap(new Process($task, $app->basePath()))->run();

                    return trim($process->getOutput()) ?: 'unreleased';
                }
            );
        }
	}
