<?php
include_once '../database/dbConnection.php';

/**
 * Class Bus
 */
class Bus
{
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
    public function __construct($bus_id = 0)
    {
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
    public function getBusDetails(): array
    {
        $query = 'SELECT * FROM ' . $this->table_name . ' WHERE id= :id LIMIT 0,1';

        // prepare query statement
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $this->bus_id);

        $stmt->execute();

        return @$stmt->fetchAll(PDO::FETCH_ASSOC)[0];
    }

    /**
     * @param $bus_id int optional bus id
     * @return array
     */
    public function getBookedSeats($bus_id = 0): array
    {
        $bus_id = $bus_id == 0 ? $this->bus_id : $bus_id;

        $query = 'SELECT
                    b.id,
                    c.fullname,
                    c.id_number,
                    c.phone,
                    c.email,
                    b.route,
                    b.seat_no,
                    buses.number_plate,
                    buses.bus_name,
                    buses.seats,
                    b.arrival_time,
                    b.departure_time,
                    b.booking_date,
                    b.paid
                FROM
                    bookings b
                LEFT JOIN customers c ON b.customer_id = c.id
                LEFT JOIN buses ON b.bus = buses.id ';

        // prepare query statement
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $this->bus_id);

        $stmt->execute();

        return @$stmt->fetchAll(PDO::FETCH_ASSOC)[0];
    }
}