<?php
include 'bus.php';

$bus = new Bus();
$bus->setBusId(1);
print_r($bus->getBusDetails());