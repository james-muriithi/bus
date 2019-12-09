<?php
class Bus{
    /**
     * @var connection
     */
    private $conn;
    /**
     * @var int
     */
    private $bus_id;

    /**
     *@param connection $conn database connection
     * @param int $bus_id bus id
     */
    public function __construct($conn, $bus_id){
        $this->conn = $conn;
        $this->bus_id = $bus_id;
    }
}