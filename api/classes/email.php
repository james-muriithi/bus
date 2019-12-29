<?php

/**
 * Class Email
 */
include 'ticket.php';
require 'vendor/autoload.php';
use AfricasTalking\SDK\AfricasTalking;

class Email extends Ticket
{
    public $from='',$subject='Seat Reservation';

    public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        parent::__construct($orientation, $unit, $size);
    }

    /**
     * @param $to string
     * @param $message string
     * @param string $cc
     * @return bool
     */
    public function sendMail($to, $message, $cc=''):bool
    {
        // To send HTML mail, the Content-type header must be set
        $headers  = "From: James Muriithi < support@theschemaqhigh.co.ke >\r\n";
        $headers .= "X-Sender: James Muriithi < support@theschemaqhigh.co.ke >\r\n";
        $headers .= 'X-Mailer: PHP/' . PHP_VERSION ."\r\n";
        $headers .= "X-Priority: 1\r\n"; // Urgent message!
        $headers .= "Return-Path: support@theschemaqhigh.co.ke\r\n"; // Return path for errors
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=iso-8859-1\r\n";
        if(!empty($cc) ){
            $headers .= 'Cc: '.$cc . "\r\n";
        }


        return @mail($to, $this->subject, $message, $headers);
    }

    /**
     * @param $to
     * @param $message
     * @param $file
     * @param $cc
     * @return bool
     */
    public function sendMailWithTicket($to, $message, $file, $cc):bool
    {
        $filename = 'ticket.pdf';
        $content = file_get_contents($file);
        $content = chunk_split(base64_encode($content));

        // a random hash will be necessary to send mixed content
        $separator = md5(time());

        // carriage return type (RFC)
        $eol = "\r\n";

//        headers
        $headers  = "From: James Muriithi < support@theschemaqhigh.co.ke >\r\n";
        $headers .= 'Cc: '.$cc . $eol;
        $headers .= "X-Sender: James Muriithi < support@theschemaqhigh.co.ke >\r\n";
        $headers .= 'X-Mailer: PHP/' . phpversion()."\r\n";
        $headers .= "X-Priority: 1\r\n"; // Urgent message!
        $headers .= "Return-Path: support@theschemaqhigh.co.ke\r\n"; // Return path for errors
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= 'Content-Type: multipart/mixed; boundary="' . $separator . '"' . $eol;
        $headers .= 'Content-Transfer-Encoding: 7bit' . $eol;
        $headers .= 'This is a MIME encoded message.' . $eol;
//        if(!empty($cc) ){
//            $headers .= 'Cc: '.$cc . $eol;
//        }

        // message
        $body = '--' . $separator . $eol;
        $body .= 'Content-Type: text/html; charset="iso-8859-1"' . $eol;
        $body .= 'Content-Transfer-Encoding: 8bit' . $eol;
        $body .= $message . $eol;

        // attachment
        $body .= '--' . $separator . $eol;
        $body .= 'Content-Type: application/octet-stream; name="' . $filename . '"' . $eol;
        $body .= 'Content-Transfer-Encoding: base64' . $eol;
        $body .= 'Content-Disposition: attachment; filename="' .$filename. '"' .$eol.$eol;
        $body .= 'X-Attachment-Id: ' . rand(1000, 99999) . $eol.$eol;
        $body .= $content . $eol;
        $body .= '--' . $separator . '--';

        return @mail($to, $this->subject, $body, $headers);
    }

    /**
     * @param $to
     * @param $message
     * @return bool
     */
    public function sendSMS($to, $message):bool
    {
        $username = 'jam_es'; // use 'sandbox' for development in the test environment
        $apiKey   = '6c1a5e29521563f1426c9a1d768357016f66c5a0adf7a7b48fdf1d0dea0e823c'; // use your sandbox app API key for development in the test environment
        $AT       = new AfricasTalking($username, $apiKey);

// Get one of the services
        $sms      = $AT->sms();

// Use the service
        $result   = $sms->send([
            'to'      => $to,
            'message' => $message
        ]);

        return $result['status'] == 'success';
    }


}