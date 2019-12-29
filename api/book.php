<?php
header('Access-Control-Allow-Origin: https://james-muriithi.github.io');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST,GET');
header('Access-Control-Max-Age: 3600');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

include 'database/dbConnection.php';
include 'classes/email.php';
include 'classes/customer.php';

$bus = new Bus();
$customer = new Customer();

if (isset($_GET['booked_seats'], $_GET['bus_id']) && !empty($_GET['bus_id'])){
    $bus_id = Database::clean($_GET['bus_id']);
    $bus->setBusId($bus_id);
    $booked = $bus->getBookedSeats($bus_id);
    echo json_encode(array_map(static function ($arr){ return $arr['seat_no'];},$booked));
}

if (isset($_GET['show_booked_seats'], $_GET['bus_id']) && !empty($_GET['bus_id'])){
    $bus_id = Database::clean($_GET['bus_id']);
    $bus->setBusId($bus_id);
    $booked = $bus->getBookedSeats($bus_id);
    echo json_encode(['data'=>$booked]);
}

if (isset($_GET['set_paid'],$_GET['id']) &&  !empty($_GET['id'])){
    $id = Database::clean($_GET['id']);

    $booked = $bus->setPaid($id);
    if ($booked){
        $bus->setBusId(1);
        $details = $bus->getBookDetails($id);
        if ($customer->sendMail('muriithijames556@gmail.com',$customer->generateMessageSendEmail($details))){
            echo json_encode(['success'=>'successfully updated and email sent to james']);
            die();
        }
        echo json_encode(['success'=>'successfully updated']);
    }else{
        echo json_encode(['error'=>'error updating payment status']);
    }
}



$data = file_get_contents("php://input");
if (isset($data) && !empty($data)) {
    $data = json_decode($data, true);
    $bus_id = $data['busId'];
    $seats = $data['seats'];
    $p_info = $data['personalInfo'];
    $from = $p_info[0]['value'];
    $to = $p_info[1]['value'];
    $name = $p_info[3]['value'];
    $id = $p_info[4]['value'];
    $phone = $p_info[5]['value'];
    $email = $p_info[6]['value'];

    $customer->setBusId($bus_id);
    $customer->setName($name);
    $customer->setPhone($phone);
    $customer->setEmail($email);
    $customer->setIdNumber($id);
    $customer->route = $from.'-'.$to;
    $customer->arrival_time = '04:00:00';
    $customer->departure_time = '04:00:00';

    $seats = explode(',',$seats);
    foreach ($seats as $seat){
        if (!$customer->seatBooked($seat)){
            if ($customer->customerExists()){
                if($customer->book($seat)){
                    if (!$customer->sendMail($customer->getEmail(),$customer->generateMessage($seat))){
                        echo json_encode(['error'=>'seat booked but unable to send email']);
                    }
                    else{
                        echo json_encode(['success'=>'Booked sucessfully']);
                    }
                }
            }else{
                if ($customer->saveCustomer()){
                    if($customer->book($seat)){
                        if (!$customer->sendMail($customer->getEmail(),$customer->generateMessage($seat))){
                            echo json_encode(['error'=>'seat booked but unable to send email']);
                        }else{
                            echo json_encode(['success'=>'Booked sucessfully']);
                        }
                    }
                }else{
                    echo json_encode(['error'=>'an unexpected error occurred']);
                }
            }
        }else{
            echo json_encode(['error'=>'seat already booked']);
        }
    }

    
    
}

//print_r($customer->getBookedSeats());