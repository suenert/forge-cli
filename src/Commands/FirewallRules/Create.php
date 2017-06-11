<?php

namespace Sven\ForgeCLI\Commands\FirewallRules;

use Sven\ForgeCLI\Commands\BaseCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Create extends BaseCommand
{
    /**
     * @var array
     */
    protected $optionMap = [
        'name' => 'name',
        'port' => 'port',
    ];

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('make:rule')
            ->addArgument('server', InputArgument::REQUIRED, 'The id of the server to create the firewall rule on.')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'The name of the firewall rule.')
            ->addOption('port', null, InputOption::VALUE_REQUIRED, 'The port to create the firewall rule for.')
            ->setDescription('Create a new firewall rule.');
    }

    /**
     * {@inheritdoc}
     */
    public function perform(InputInterface $input, OutputInterface $output)
    {
        $this->forge->createFirewallRule(
            $input->getArgument('server'), $this->fillData($input->getOptions()), false
        );
    }
}
