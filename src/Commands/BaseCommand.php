<?php

namespace Sven\ForgeCLI\Commands;

use Sven\FileConfig\Drivers\Json;
use Sven\FileConfig\File;
use Sven\FileConfig\Store;
use Sven\ForgeCLI\Contracts\NeedsForge;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Themsaid\Forge\Forge;

abstract class BaseCommand extends Command
{
    /**
     * @var \Themsaid\Forge\Forge
     */
    protected $forge;

    /**
     * @var \Sven\FileConfig\Store
     */
    protected $config;

    /**
     * @var array
     */
    protected $optionMap = [];

    /**
     * @param \Themsaid\Forge\Forge|null $forge
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     * @throws \LogicException
     */
    public function __construct(Forge $forge = null)
    {
        parent::__construct();

        $this->config = $this->getFileConfig();

        if ($this instanceof NeedsForge) {
            $this->forge = $forge ?: new Forge($this->config->get('key'));
        }
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        // If the 'site' argument is present, the user probably did not
        // use an alias, so we will return early. If it is missing,
        // resolve the alias and set the arguments accordingly.
        if ($input->hasArgument('site') && $input->getArgument('site') !== null) {
            return;
        }

        $alias = $this->config->get(
            'aliases.'.$input->getArgument('server')
        );

        // No alias was found by that name, so we will
        // continue executing the command here. This
        // will cause a validation error later on.
        if ($alias === null) {
            $output->writeln("<error>No alias found for '{$input->getArgument('server')}'.</error>");

            return;
        }

        // Could not find alias for site, continue executing the
        // command to cause an error later on by Symfony's own
        // validation that takes place after this method.
        if ($input->hasArgument('site') && ! isset($alias['site'])) {
            $output->writeln("<error>No site alias found, but a site is required for this command.</error>");

            return;
        }

        if (! $output->isQuiet()) {
            $output->writeln("<comment>Using aliased server '{$alias['server']}' and site '{$alias['site']}'.</comment>");
        }

        $input->setArgument('server', $alias['server']);
        $input->setArgument('site', $alias['site']);
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param array                                             $header
     * @param array                                             $rows
     */
    protected function table(OutputInterface $output, array $header, array $rows)
    {
        $table = new Table($output);
        $table->setHeaders($header)
            ->setRows($rows);

        $table->render();
    }

    /**
     * @param array      $options
     * @param array|null $optionMap
     *
     * @return array
     */
    protected function fillData(array $options, array $optionMap = null)
    {
        $data = [];

        foreach ($optionMap ?: $this->optionMap as $option => $requestKey) {
            if (! isset($options[$option])) {
                continue;
            }

            $data[$requestKey] = $options[$option];
        }

        return $data;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param string                                          $option
     *
     * @return bool|string
     */
    protected function getFileContent(InputInterface $input, $option)
    {
        $filename = $input->hasOption($option) ? $input->getOption($option) : 'php://stdin';

        if (! file_exists($filename)) {
            return $filename;
        }

        if ($filename && ftell(STDIN) !== false) {
            return file_get_contents($filename);
        }

        throw new \InvalidArgumentException('This command requires either the "--'.$option.'" option to be set, or an input from STDIN.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param array                                           ...$keys
     *
     * @throws \RuntimeException
     */
    protected function requireOptions(InputInterface $input, ...$keys)
    {
        foreach ($keys as $key) {
            if ($input->hasOption($key)) {
                continue;
            }

            throw new \RuntimeException(
                sprintf('The option "%s" is required.', $key)
            );
        }
    }

    /**
     * @return \Sven\FileConfig\Store
     */
    protected function getFileConfig()
    {
        $home = strncasecmp(PHP_OS, 'WIN', 3) === 0 ? $_SERVER['USERPROFILE'] : $_SERVER['HOME'];
        $configFile = $home.DIRECTORY_SEPARATOR.'forge.json';

        if (! file_exists($configFile)) {
            file_put_contents($configFile, '{}');
        }

        return new Store(new File($configFile), new Json());
    }
}
