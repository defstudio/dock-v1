<?php


	namespace App\Services;


	use Illuminate\Console\OutputStyle;
    use Symfony\Component\Process\Process;

    class TerminalService{
        /** @var OutputStyle $output */
	    private $output;

        public function init($output){
            $this->output = $output;
        }


        public function execute(array $commands, string $input=null, array $environment_variables = null): int{
            $process = new Process(command: $commands, env: $environment_variables);

            if(!empty($input)) $process->setInput($process);

            $process->setTty(Process::isTtySupported());
            $process->setTimeout(null);
            $process->setIdleTimeout(null);

            return $process->run(function($type, $buffer){
                if(!empty($this->output)){
                    $this->output->write($buffer);
                }
            });
        }

        public function execute_in_shell_command_line(array $commands): int{
            $process = Process::fromShellCommandline(implode(' ', $commands));

            $process->setTty(Process::isTtySupported());
            $process->setTimeout(null);
            $process->setIdleTimeout(null);

            return $process->run(function($type, $buffer){
                if(!empty($this->output)){
                    $this->output->write($buffer);
                }
            });
        }


	}
