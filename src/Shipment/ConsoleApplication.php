<?php

namespace Shipment;

use Shipment\Command\ShippmentTrackCommand;
use Symfony\Component\Console\Application;

class ConsoleApplication extends Application
{
    private $apiClient;

    /**
     * 
     * @param PolishPostTracking\Api $apiClient
     */
    public function __construct(\PolishPostTracking\Api $apiClient)
    {
        parent::__construct('Shipment tracking app', '1.0');
        $this->apiClient = $apiClient;
                
        $this->add(new ShippmentTrackCommand($this->apiClient));
    }

}
