<?php

namespace Sven\ForgeCLI\Commands\Daemons;

use Sven\ForgeCLI\Contracts\NeedsForge;
use Themsaid\Forge\Resources\Daemon;
use Sven\ForgeCLI\Commands\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class All extends BaseCommand implements NeedsForge
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('list:daemons')
            ->addArgument('server', InputArgument::REQUIRED, 'The id of the server to list the daemons for.')
            ->setDescription('Show all daemons running on a server.');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $rows = array_map(function (Daemon $daemon) {
            return [$daemon->id, $daemon->status, $daemon->command, $daemon->createdAt];
        }, $this->forge->daemons($input->getArgument('server')));

        $this->table($output, ['Id', 'Status', 'Command', 'Created'], $rows);
    }
}
