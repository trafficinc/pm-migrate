<?php

namespace App;

class Cli
{

    protected CliPrinter $printer;

    protected array $registry = [];

    public function __construct()
    {
        $this->printer = new CliPrinter();
    }

    public function getPrinter(): CliPrinter
    {
        return $this->printer;
    }

    public function registerCommand(string $name, callable $callable): void
    {
        $this->registry[$name] = $callable;
    }

    public function getCommand($command)
    {
        return isset($this->registry[$command]) ? $this->registry[$command] : null;
    }

    public function runCommand(array $argv = [])
    {
        $command_name = "help";

        if (isset($argv[1])) {
            $command_name = $argv[1];
        }

        $command = $this->getCommand($command_name);

        if ($command === null) {
            $this->getPrinter()->display("ERROR: Command \"$command_name\" not found.");
            exit;
        }

        call_user_func($command, $argv);
    }
}
