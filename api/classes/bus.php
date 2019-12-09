<?php
include_once '../database/dbConnection.php';

/**
 * Class Bus
 */
class Bus{
    /**
     * @var PDO
     * @var string
     */
    private $conn, $table_name = 'buses';
    /**
     * @var int
     */
    private $bus_id;

    /**
     * @param int $bus_id bus id
     */
    public function __construct($bus_id = 0){
        $database = new Database();
        $this->conn = $database::getInstance();
        $this->bus_id = $bus_id;
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
    public function setBusId($bus_id): void
    {
        $this->bus_id = $bus_id;
    }

    /**
     * @return mixed
     */
    public function getBusDetails()
    {
        $query = 'SELECT * FROM ' .$this->table_name. ' WHERE id= :id LIMIT 0,1';

        // prepare query statement
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id',$this->bus_id);

        $stmt->execute();

        return @$stmt->fetchAll(PDO::FETCH_ASSOC)[0];
    }
}