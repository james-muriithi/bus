<?php
date_default_timezone_set('Africa/Nairobi');
/**
 * Class Bus
 */
class Bus extends Email
{
    public $conn;
    /**
     * @var int
     */
    private $bus_id;

    /**
     * @param int $bus_id bus id
     */
    public function __construct($bus_id = 0)
    {
        parent::__construct();
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

    public function getBookDetails($id,$bus_id = ''):array
    {
        $bus_id = ($bus_id == '' )? $this->bus_id : $bus_id;
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
                LEFT JOIN buses ON b.bus = buses.id 
                WHERE 
                    buses.id=:bid 
                AND
                    b.id =:id';

        // prepare query statement
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':bid', $bus_id);

        $stmt->execute();

        return @$stmt->fetchAll(PDO::FETCH_ASSOC)[0];
    }

    /**
     * @param $id
     * @return bool
     */
    public function setPaid($id):bool
    {
        $query = 'UPDATE bookings SET paid = 1 WHERE id =:id';

        // prepare query statement
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id);

        $stmt->execute();

        return $stmt->execute();
    }

    /**
     * @return array
     */
    public function getBusDetails(): array
    {
        $query = 'SELECT * FROM buses WHERE id = :id LIMIT 0,1';

        // prepare query statement
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $this->bus_id);

        $stmt->execute();

        return @$stmt->fetchAll(PDO::FETCH_ASSOC)[0];
    }

    /**
     * @param $bus_id string optional bus id
     * @return array
     */
    public function getBookedSeats($bus_id = ''): array
    {
        $bus_id = ($bus_id == '' )? $this->bus_id : $bus_id;
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
                LEFT JOIN buses ON b.bus = buses.id 
                WHERE 
                    buses.id=:id ';

        // prepare query statement
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $bus_id);

        $stmt->execute();

        return @$stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param $seat_no
     * @param string $date
     * @return string
     */
    public function seatBooked($seat_no, $date = ''):string
    {
        $date = empty($date)? date('Y-m-d',strtotime('2019-12-27')): date('Y-m-d', strtotime($date));

        $query = 'SELECT 
                    *
                  FROM
                    bookings
                  WHERE 
                    seat_no=:seat_no
                  AND 
                    DATE(travel_date) = :date';

        // prepare query statement
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':seat_no', $seat_no);
        $stmt->bindParam(':date', $date);

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }


}