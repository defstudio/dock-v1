<?php


	namespace App\Services;


	use Symfony\Component\Process\Process;

    class TerminalService{
	    private $output;

        public function init($output){
            $this->output = $output;
        }


        public function execute(array $commands): int{
            $process = new Process($commands);

            $process->setTty(Process::isTtySupported());
            $process->setTimeout(null);
            $process->setIdleTimeout(null);

            return $process->run(function($type, $buffer){
                $this->output->write($buffer);
            });
        }
	}
