<?php

namespace Shipment\Command;

use PolishPostTracking\Api;
use stdClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ShippmentTrackCommand extends Command
{
    /**
     *
     * @var Api
     */
    private $client;

    /**
     * 
     * @param PolishPostTracking\Api $client
     */
    public function __construct(Api $client) {
        $this->client = $client;
        
        parent::__construct();
    }

    protected function configure() {
        $this->setName('track')
                ->setDescription('Display tracking info about shipment sent by Polish Post. Use testp0 for tests')
                ->addArgument('package_number', InputArgument::REQUIRED, 'Tracking number')
                ->addOption('all', 'a', InputOption::VALUE_NONE, 'Show detailed information including history of operations');

        parent::configure();
    }

    /**
     * Executes the command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $number = $input->getArgument('package_number');

        $result = $this->client->checkPackage($number);
        if ($result->status != 0) {
            $output->writeln('<error>Failed to retrieve shipment data</error>');
            return 1;
        }

        $output->writeln('');
        $this->renderBasicInfo($output, $result);
        
        if ($input->getOption('all')) {
            $output->writeln('');
            $this->renderDetails($output, $result);
        }

        return 0;
    }

    /**
     * Renders basic info about the shipment to output
     * 
     * @param OutputInterface $output
     * @param stdClass $result
     */
    protected function renderBasicInfo(OutputInterface $output, stdClass $result ) {
        $output->writeln('<info>Shipment data</info>');
        $this->getTable($output)
                ->setRows(array(
                    array('Package number', $result->numer),
                    array('Posting date', $result->danePrzesylki->dataNadania),
                    array('Package type', $result->danePrzesylki->rodzPrzes),
                    array('The office of origin', $result->danePrzesylki->urzadNadania->nazwa),
                    array('The office of destination', $result->danePrzesylki->urzadPrzezn->nazwa),
                    array('Weight', $result->danePrzesylki->masa),
                    array('Is delivered', $result->danePrzesylki->zakonczonoObsluge ? 'Yes' : 'No')
                ))
                ->render();
    }

    /**
     * Renders detailed information about the shipment to output
     * 
     * @param OutputInterface $output
     * @param stdClass $result
     */
    protected function renderDetails(OutputInterface $output, stdClass $result ) {
        $actions = array();
        foreach ($result->danePrzesylki->zdarzenia->zdarzenie as $action) {
            $actions[] = array(
                $action->jednostka->nazwa,
                $action->czas,
                $action->nazwa
            );
        }

        $output->writeln('<info>History of operations</info>');
        $this->getTable($output)
                ->setHeaders(array(
                    'Location',
                    'Date',
                    'Action'
                ))
                ->setRows($actions)
                ->render();
    }

    /**
     * Get instance of table helper
     * 
     * @param OutputInterface $output
     * @return Table
     */
    private function getTable(OutputInterface $output)
    {
        return new Table($output);
    }

}
