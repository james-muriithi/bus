<?php
include_once '../database/dbConnection.php';
class Bus{
    /**
     * @var PDO
     */
    private $conn;
    /**
     * @var int
     */
    private $bus_id;

    /**
     * @param int $bus_id bus id
     */
    public function __construct($bus_id){
        $database = new Database();
        $this->conn = $database::getInstance();
        $this->bus_id = $bus_id;

        print_r($this->conn);
    }

    /**
     * @return int
     */
    public function getBusId()
    {
        return $this->bus_id;
    }

    /**
     * @param int $bus_id
     */
    public function setBusId($bus_id)
    {
        $this->bus_id = $bus_id;
    }
}