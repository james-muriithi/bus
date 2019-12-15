<?php
header('Access-Control-Allow-Origin: https://james-muriithi.github.io');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Max-Age: 3600');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

include 'database/dbConnection.php';
include 'classes/email.php';
include 'classes/customer.php';

if (isset($_GET['bid'],$_GET['seat_no'])){
    $customer = new Customer();
    $bus_id = $_GET['bid'];
    $customer->setBusId($bus_id);
    $seat_no = $_GET['seat_no'];
//    print_r($customer->getTicketDetails($seat_no));

    if ($customer->getTicketDetails($seat_no)['paid'] == 1){
        $customer->printTicket($seat_no);
        $file = './tickets/'.$seat_no.'.pdf';
        if (!file_exists($file)){
            echo json_encode(['error'=>'ticket file does not exist']);
            exit();
        }
        if (!$customer->sendMailWithTicket($customer->getTicketDetails($seat_no)['email'],$customer->generateMessageTicket($seat_no),$file)){
            echo json_encode(['error'=>'unable to send ticket']);
        }else{
            echo json_encode(['success'=>'ticket sent']);
        }
    }else{
        echo json_encode(['error'=>'ticket not paid for']);
        exit();
    }
}