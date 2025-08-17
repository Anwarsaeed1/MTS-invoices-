<?php

namespace AnwarSaeed\InvoiceProcessor\Core;

use AnwarSaeed\InvoiceProcessor\Contracts\Commands\CommandInterface;

class CommandHandler
{
    private array $commands = [];

    public function registerCommand(CommandInterface $command): void
    {
        $this->commands[$command->getName()] = $command;
    }

    public function execute(string $commandName, array $args = []): void
    {
        if (!isset($this->commands[$commandName])) {
            throw new \InvalidArgumentException("Unknown command: {$commandName}");
        }

        $this->commands[$commandName]->execute($args);
    }

    public function getAvailableCommands(): array
    {
        $available = [];
        foreach ($this->commands as $name => $command) {
            $available[$name] = $command->getDescription();
        }
        return $available;
    }

    public function hasCommand(string $commandName): bool
    {
        return isset($this->commands[$commandName]);
    }
}
