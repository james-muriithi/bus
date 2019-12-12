<?php

/**
 * Class Email
 */
class Email
{
    public $from='',$subject='Seat Reservation';

    /**
     * @param $to string
     * @param $message string
     * @return bool
     */
    public function sendMail($to, $message):bool
    {
        // To send HTML mail, the Content-type header must be set
        $headers  = "From: James Muriithi < support@theschemaqhigh.co.ke >\r\n";
        $headers .= "X-Sender: James Muriithi < support@theschemaqhigh.co.ke >\r\n";
        $headers .= 'X-Mailer: PHP/' . phpversion()."\r\n";
        $headers .= "X-Priority: 1\r\n"; // Urgent message!
        $headers .= "Return-Path: support@theschemaqhigh.co.ke\r\n"; // Return path for errors
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=iso-8859-1\r\n";


        return @mail($to, $this->subject, $message, $headers);
    }



}