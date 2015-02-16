<?php

use PolishPostTracking\Api;
use Shipment\ConsoleApplication;

require 'vendor/autoload.php';

$app = new ConsoleApplication(new Api());
$app->run();
