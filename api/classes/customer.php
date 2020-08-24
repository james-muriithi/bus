f<?php
include 'bus.php';
/**
 * Class Customer
 */
class Customer extends Bus{
    private $name, $email, $phone, $id_number;

    public $arrival_time, $departure_time,$route;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return bool
     */
    public function saveCustomer():bool
    {
        $query = 'INSERT INTO
                    customers
                  SET 
                    fullname= :name,
                    phone= :phone,
                    id_number = :id_number,
                    email=:email';
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':id_number', $this->id_number);
        $stmt->bindParam(':email', $this->email);

        return $stmt->execute();
    }

    /**
     * @param string $id_number
     * @return array
     */
    public function getCustomerInfo($id_number = ''):array
    {
        $id_number = empty($id_number)? $this->id_number : $id_number;
        $query = 'SELECT 
                    *
                  FROM
                    customers
                  WHERE 
                    id_number= :id_number
                  LIMIT 0,1';
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id_number', $id_number);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
    }

    /**
     * @param string $seat_no
     * @return bool
     */
    public function book(string $seat_no):bool
    {
        $query = 'INSERT INTO
                    bookings
                  SET 
                    customer_id=:cid,
                    route=:route,
                    seat_no=:seat_no,
                    bus=:bus_id,
                    arrival_time=:arrival_time,
                    departure_time=:departure_time';
        $stmt = $this->conn->prepare($query);
        $cid = @$this->getCustomerInfo()['id'];
        $bus_id = $this->getBusId();

        $stmt->bindParam(':cid',$cid);
        $stmt->bindParam(':route',$this->route);
        $stmt->bindParam(':seat_no',$seat_no);
        $stmt->bindParam(':bus_id',$bus_id);
        $stmt->bindParam(':arrival_time',$this->arrival_time);
        $stmt->bindParam(':departure_time',$this->departure_time);

        return $stmt->execute();
    }

    /**
     * @param int $bus_id
     * @return array
     */
    public function getBookedSeats($bus_id = 0): array
    {
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
                    c.id_number = :id';
        // prepare query statement
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $this->id_number);

        $stmt->execute();

        return @$stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReceiptId($seat_no):int
    {
        $query = 'SELECT
                    b.id
                FROM
                    bookings b
                WHERE 
                    b.bus = :bid
                AND 
                    b.seat_no = :seat_no';
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $bid = $this->getBusId();

        $stmt->bindParam(':bid', $bid);
        $stmt->bindParam(':seat_no', $seat_no);

        $stmt->execute();

        return @$stmt->fetchAll(PDO::FETCH_ASSOC)[0]['id'];
    }

    /**
     * @param string $id_number
     * @return bool
     */
    public function customerExists(string $id_number = ''):bool
    {
        $id_number = empty($id_number)? $this->id_number : $id_number;
        $query = 'SELECT 
                    *
                  FROM
                    customers
                  WHERE 
                    id_number= :id_number
                  LIMIT 0,1';
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id_number', $id_number);

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    private function addBody($seat_no):void
    {
        $ticketDetails = $this->getTicketDetails($seat_no);
        // body

        $this->pdf->SetFillColor(249, 249, 249);
        $this->pdf->Rect(0,20,$this->pdf->GetPageWidth(),$this->pdf->GetPageHeight(),'F');


        $this->pdf->SetFont('moonl','',12);
        $this->pdf->setTextColor(0,0,0);
        $y = $this->pdf->GetY();
        $this->pdf->SetXY(7,$y+18);
        $this->pdf->MultiCell($this->pdf->GetPageWidth()-70,7,$ticketDetails['number_plate']. ' - '
            .$ticketDetails['bus_name'],0, 'L');

        // name label
        $this->pdf->SetFont('coves','',9);
        $this->pdf->SetTextColor(0, 51, 204);
        $this->pdf->SetXY(12,$y+25);
        $this->pdf->Cell(10,7, 'Name:',0, 'L');

//        name
        $this->pdf->SetFont('bahaus','',15);
        $this->pdf->SetTextColor(13, 13, 13);
        $this->pdf->SetY($y+28);
        $this->pdf->Cell(12,10,$ticketDetails['fullname'],0,0,'L');

        // arrival label
        $y = $this->pdf->GetY();
        $this->pdf->SetFont('coves','',9);
        $this->pdf->SetTextColor(0, 51, 204);
        $this->pdf->SetXY(12,$y+10);
        $this->pdf->Cell(10,7, 'Arrival:',0, 'L');

//        arrival
        $this->pdf->SetFont('bahaus','',15);
        $this->pdf->SetTextColor(13, 13, 13);
        $this->pdf->SetY($y+13);
        $this->pdf->Cell(12,10,$ticketDetails['arrival_time'],0,0,'L');

        // departure label
        $this->pdf->SetFont('coves','',9);
        $this->pdf->SetTextColor(0, 51, 204);
        $this->pdf->SetXY(50,$y+10);
        $this->pdf->Cell(10,7, 'Departure:',0, 'L');

//        departure
        $this->pdf->SetFont('bahaus','',15);
        $this->pdf->SetTextColor(13, 13, 13);
        $this->pdf->SetX(50);
        $this->pdf->SetXY(50,$y+13);
        $this->pdf->Cell(12,10,$ticketDetails['departure_time'],0,0,'L');


//        row 2
        // from label
        $y = $this->pdf->GetY();
        $this->pdf->SetFont('coves','',9);
        $this->pdf->SetTextColor(0, 51, 204);
        $this->pdf->SetXY(12,$y+10);
        $this->pdf->Cell(10,7, 'From:',0, 'L');

//        from
        $this->pdf->SetFont('bahaus','',15);
        $this->pdf->SetTextColor(13, 13, 13);
        $this->pdf->SetY($y+13);
        $this->pdf->Cell(12,10,@explode('-',$ticketDetails['route'])[0],0,0,'L');

        // to label
        $this->pdf->SetFont('coves','',9);
        $this->pdf->SetTextColor(0, 51, 204);
        $this->pdf->SetXY(50,$y+10);
        $this->pdf->Cell(10,7, 'To:',0, 'L');

//        to
        $this->pdf->SetFont('bahaus','',15);
        $this->pdf->SetTextColor(13, 13, 13);
        $this->pdf->SetX(50);
        $this->pdf->SetXY(50,$y+13);
        $this->pdf->Cell(12,10,@explode('-',$ticketDetails['route'])[1],0,0,'L');


        // ticket no label
        $y = $this->pdf->GetY();
        $this->pdf->SetFont('coves','',9);
        $this->pdf->SetTextColor(0, 51, 204);
        $this->pdf->SetXY(12,$y+10);
        $this->pdf->Cell(10,7, 'Seat No:',0, 'L');

//        ticket no
        $this->pdf->SetFont('bahaus','',15);
        $this->pdf->SetTextColor(13, 13, 13);
        $this->pdf->SetY($y+13);
        $this->pdf->Cell(12,10,$seat_no,0,0,'L');

        // date label
        $this->pdf->SetFont('coves','',9);
        $this->pdf->SetTextColor(0, 51, 204);
        $this->pdf->SetXY(50,$y+10);
        $this->pdf->Cell(10,7, 'Travel Date:',0, 'L');

//        date
        $this->pdf->SetFont('bahaus','',15);
        $this->pdf->SetTextColor(13, 13, 13);
        $this->pdf->SetX(50);
        $this->pdf->SetXY(50,$y+13);
        $this->pdf->Cell(12,10,'27-12-2019',0,0,'L');


        // // footer
        $this->pdf->setXY($this->pdf->GetPageWidth()- 40,$this->pdf->GetPageHeight()-20);
        $this->pdf->setTextColor(159,2,2);
        $this->pdf->SetFont('bahaus','',17);
        $this->pdf->Cell(10,5,'Ticket #'.$ticketDetails['id'],0,"L");

        $this->pdf->setXY($this->pdf->GetPageWidth()- 30,$this->pdf->GetPageHeight()-5);
        $this->pdf->setTextColor(77, 77, 77);
        $this->pdf->SetFont('bahaus','',9);
        $this->pdf->Cell(10,5,$ticketDetails['booking_date'],0,"L");

    }

    public function printTicket($seat_no):void
    {
        $this->pdf = new exFPDF('L','mm',array(95,200));
        $this->pdf->AddPage();
        $this->pdf->SetAutoPageBreak(false);
        $this->pdf->SetFillColor(159,2,2);
        $this->pdf->Rect(0,0,$this->pdf->GetPageWidth(),20,'F');

        $this->addFonts();
        $this->addLogo('');
        $this->addBody($seat_no);
        $this->generateQR($seat_no);
        $this->pdf->Output('F','tickets/'.$seat_no.'.pdf');
    }

    public function generateQR($seat_no):void
    {
        $ticketDetails = $this->getTicketDetails($seat_no);
        $codeContents = 'url:https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        // $codeContents .= ', event id:'.$event_id;
        $codeContents .= ';ticket no:'.$ticketDetails['id'];
        QRcode::png($codeContents, 'temp/'.$ticketDetails['fullname'].$ticketDetails['id'].'.png', QR_ECLEVEL_L, 3);


        $this->pdf->image( 'temp/'.$ticketDetails['fullname'].$ticketDetails['id'].'.png',$this->pdf->GetPageWidth()-50,25,-76);
    }
//use ticket id next time
    public function getTicketDetails($seat_no):array
    {
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
                    b.seat_no = :seat_no';
        // prepare query statement
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':seat_no', $seat_no);

        $stmt->execute();

        return @$stmt->fetchAll(PDO::FETCH_ASSOC)[0];
    }

    public function generateSMS($seat_no):string
    {
        $ticketDetails = $this->getTicketDetails($seat_no);
        $message = 'Dear '.$ticketDetails['fullname'].', your payment was successsfully received. A ticket was sent to your email address '
            .$ticketDetails['email'].'. Arrival time at '.@explode('-',$ticketDetails['route'])[0] .' is'
            .$ticketDetails['arrival_time'].'. Your seat number is '.$ticketDetails['seat_no']
                        .'. Have a safe journey and blessed holidays.';
        return $message;
    }

    function generateMessageSendEmail($details):string
    {
        $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Turn off iOS phone number autodetect -->
    <meta name="format-detection" content="telephone=no" />
    <meta name="format-detection" content="address=no" />

    <!--[if mso]>
    <style>
        * { font-family: sans-serif !important; }
    </style>
    <![endif]-->
    <style type="text/css">
        /*
         * Styles for all the emails
         */
        /* Normalizing */

        #outlook a {
            padding: 0;
        }

        * {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }

        html,
        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            font-weight: normal;
            width: 100% !important;
            height: 100% !important;
            margin: 0;
            padding: 0;
        }

        img {
            border: 0;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
            height: auto;
            line-height: 100%;
            display: inline-block;
        }

        a img {
            border: none;
            display: block;
        }

        h1 {
            color: #1E0A3C !important;
        }

        h2,
        h3,
        h4,
        h5,
        h6 {
            color: #1E0A3C !important;
        }

        h1 a,
        h2 a,
        h3 a,
        h4 a,
        h5 a,
        h6 a {
            color: #3F60E7 !important;
        }

        h1 a:active,
        h2 a:active,
        h3 a:active,
        h4 a:active,
        h5 a:active,
        h6 a:active {
            color: #3F60E7 !important;
        }

        h1 a:visited,
        h2 a:visited,
        h3 a:visited,
        h4 a:visited,
        h5 a:visited,
        h6 a:visited {
            color: #3F60E7 !important;
        }

        table {
            border-collapse: collapse !important;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        table td {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        table {
            border-spacing: 0;
            border: 0;
            padding: 0;
            table-layout: fixed;
            margin: 0;
        }

        .no_text_resize {
            -moz-text-size-adjust: none;
            -webkit-text-size-adjust: none;
            -ms-text-size-adjust: none;
            text-size-adjust: none;
        }

        a[href^="x-apple-data-detectors:"] {
            color: inherit !important;
            text-decoration: none !important;
            font-size: inherit !important;
            font-family: inherit !important;
            font-weight: inherit !important;
            line-height: inherit !important;
        }

        td[class="grid__col"] {
            padding-left: 60px !important;
            padding-right: 60px !important;
        }

        .grid__col {
            padding-left: 60px !important;
            padding-right: 60px !important;
        }
        /* Footer */

        .footer-content.bottom-section {
            padding: 0px 32px;
        }

        .content .align-center,
        .footer-content .align-center {
            text-align: center;
        }

        .footer-content {
            color: #B7B6C0 !important;
        }

        .footer-content a {
            color: #f05537 !important;
            text-decoration: none;
        }
        /* Social */

        .social-logo-container {
            display: inline-block;
            height: auto;
            margin: 0px;
        }

        .content {
            width: 600px;
        }
        /* Media Queries */

        @media all and (max-width: 600px) {
            .content {
                /* slight space on each side */
                width: 97% !important;
            }
            *[class="gmail-fix"] {
                display: none !important;
            }
            td[class="grid__col"] {
                padding-left: 0px !important;
                padding-right: 0px !important;
                max-width: 90% !important;
            }
            .grid__col {
                padding-left: 0px !important;
                padding-right: 0px !important;
                max-width: 90% !important;
            }
            .social-logo-container {
                display: inline-block !important;
            }
            .btn {
                font-size: 15px !important;
                padding: 12px 28px 14px !important;
                margin-bottom: 18px;
            }
            .hide {
                display: none;
            }
        }
    </style>

    <!--[if (mso)|(IE)]>
    <xml:namespace ns="urn:schemas-microsoft-com:vml" prefix="v" />
    <style>
        v\: * { behavior:url(#default#VML); display:inline-block }
        .container-table {
            width: 100%;
        }
    </style>
    <!<![endif]-->
    <!--[if (gte mso 9)|(IE)]>
    <style>
        .hide {
            display: none;
        }
    </style>
    <![endif]-->

</head>

<!-- Global container with background styles. Gmail converts BODY to DIV so we lose properties like BGCOLOR. -->

<body style="
        padding:0px !important;
        margin:0px !important;
    ">
<div class="hide" style="letter-spacing:596px;line-height:0;mso-hide:all"></div>
<!-- Outermost Wrapper Table -->
<table style="min-width:100%;" width="100%" bgcolor="#EEEDF2">
    <tr>
        <td>
            <table style="
        margin-left:auto;
        margin-right:auto;

        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    " cellpadding="0" cellspacing="0" border="0" class="container-table">
                <tr>
                    <!-- Centered 600px Email Table -->
                    <td style="
        margin-left:auto;
        margin-right:auto;
    " align="center" bgcolor="#EEEDF2">
                        <table class="content" style="border: none;">
                            <tr>
                                <td align="center" bgcolor="#EEEDF2">
                                    <table cellpadding="0" cellspacing="0" border="0" bgcolor="#EEEDF2">
                                        <tbody>

                                        <!-- Body Section -->

                                        <tr>
                                            <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;background-color:#EEEDF2;" width="600" height="18">
                                                <table style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="18">

                                                    <tr>
                                                        <td style="mso-line-height-rule:exactly;line-height:36px;font-size:36px;height:36px;" height="36" bgcolor=""></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr bgcolor="#EEEDF2">
                                            <td style="background-color:#EEEDF2;" bgcolor="#EEEDF2" class="grid__col">
                                                <table style="
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
        width:100%;
    " align="center">
                                                    <tr class="row_section" style="" bgcolor="#EEEDF2">
                                                        <td width="30" bgcolor="#EEEDF2" align="center"></td>
                                                        <td class="" style="text-align:center;background-color:#EEEDF2;" align="center">
                                                            <table style="
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
        width:100%;
    " class="" cellspacing="0" cellpadding="0" bgcolor="#EEEDF2" align="center">

                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="">

                                                                        <div style="
        width: 140px;
        height: 25px;
        margin: 0 auto;
        text-align: center;
    ">

                                                                            <img src=\'https://examinationcomplaint.theschemaqhigh.co.ke/HCI/bus.png\' title=\'\' alt=\'Logo\' style=\'\' border="0" width=\'140\' height=\'25\' class="" />

                                                                        </div>

                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;background-color:#EEEDF2;" width="600" height="18">
                                                                        <table style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="18">

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:36px;font-size:36px;height:36px;" height="36" bgcolor=""></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>

                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="">

                                                                        <h1 style="
        padding: 0;
        margin: 12px 0 0 0;

        font-size: 25px;
        line-height: 42px;
        font-weight: bold;
        font-weight: 800;
        letter-spacing: -0.2px;
        color: #1E0A3C;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
    color: #1E0A3C; margin-top:0;" class="h1-header">

                                                                            James, <br />another seat has been paid for

                                                                        </h1>

                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;background-color:#EEEDF2;" width="600" height="18">
                                                                        <table style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="18">

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:36px;font-size:36px;height:36px;" height="36" bgcolor=""></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>

                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="">

                                                                        <img src=\'https://theschemaqhigh.co.ke/examinationcomplaint/HCI/booked.png\' title=\'\' alt=\'Eventbrite\' style=\'\' border="0" width=\'115\' height=\'115\' class="" />

                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="mso-line-height-rule:exactly;line-height:8px;font-size:8px;background-color:#EEEDF2;" width="600" height="8">
                                                                        <table style="mso-line-height-rule:exactly;line-height:8px;font-size:8px;height:8px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="8">

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:16px;font-size:16px;height:16px;" height="16" bgcolor=""></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>

                                                            </table>
                                                        </td>
                                                        <td width="30" bgcolor="#EEEDF2"></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr bgcolor="#EEEDF2">
                                            <td style="background-color:#EEEDF2;" bgcolor="#EEEDF2" class="grid__col">
                                                <table style="
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
        width:100%;
    " align="center">
                                                    <tr class="row_section" style="" bgcolor="#EEEDF2">
                                                        <td width="30" bgcolor="#EEEDF2" align="center"></td>
                                                        <td class="" style="text-align:center;background-color:#EEEDF2;" align="center">
                                                            <table style="
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
        width:100%;
    color:#1E0A3C;" class="" cellspacing="0" cellpadding="0" bgcolor="#EEEDF2" align="center">

                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="">


                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="mso-line-height-rule:exactly;line-height:12px;font-size:12px;background-color:#EEEDF2;" width="600" height="12">
                                                                        <table style="mso-line-height-rule:exactly;line-height:12px;font-size:12px;height:12px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="12">

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:24px;font-size:24px;height:24px;" height="24" bgcolor=""></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>

                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="">

                                                                        <!--[if (mso)|(ie)]>
                                                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="https://www.eventbrite.com/eventbriteapp?utm_campaign=order_confirm&amp;utm_medium=email&amp;ref=eemailordconf&amp;app_cta_src=order_conf_email&amp;utm_source=eb_email&amp;utm_term=downloadapp" style="height:40px;v-text-anchor:middle;width:225px;" arcsize="10%" strokeweight="1px" strokecolor="#f05537" fillcolor="#f05537">
                                                            <w:anchorlock/>
                                                            <center style="color:#FFFFFF;font-family:sans-serif;font-size:16px;font-weight:normal;">
                                                                Get the app
                                                            </center>
                                                        </v:roundrect>
                                                        <![endif]-->
                                                                        <!--[if !((mso)|(ie))]><!-- -->
                                                                        <div style="text-align:center;
        font-family: &#39;Helvetica Neue&#39;, Helvetica, Arial, sans-serif;

        padding: 0;
    ">
                                                                            <a 
                      href="https://examinationcomplaint.theschemaqhigh.co.ke/HCI/api/print-ticket.php?bid=1&seat_no='.$details['seat_no'].'" target="_blank" style="
                background-color:#f05537;
                color:#FFFFFF;
                border-color:#f05537;

        display: inline-block;
        line-height: 22px;
        font-weight: normal;
        font-weight: 500;
        font-size: 15px;
        text-align: center;
        text-decoration: none;
        padding: 8px 28px 12px;
        border-radius: 4px;
        border-style: solid;
        border-width: 2px;

            " class="btn ">
                                                                                Send Ticket
                                                                            </a>
                                                                        </div>
                                                                        <!--<![endif]-->

                                                                    </td>
                                                                </tr>

                                                            </table>
                                                        </td>
                                                        <td width="30" bgcolor="#EEEDF2"></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;background-color:#EEEDF2;" width="600" height="18">
                                                <table style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="18">

                                                    <tr>
                                                        <td style="mso-line-height-rule:exactly;line-height:36px;font-size:36px;height:36px;" height="36" bgcolor=""></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;background-color:#FFFFFF;
    border-top-left-radius:4px;border-top-right-radius:4px;border-bottom-right-radius:0;border-bottom-left-radius:0;
    " width="600" height="18">
                                                <table style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="18">

                                                    <tr>
                                                        <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;" height="18" bgcolor=""></td>
                                                    </tr>
                                                    <tr bgcolor="#FFFFFF">
                                                        <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;" height="18"></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
<!-- ------------------------------------------------------------------------ -->
<!------------------------------------------------------------------------------------ -->
                                        <tr bgcolor="#FFFFFF">
                                            <td style="background-color:#FFFFFF;" bgcolor="#FFFFFF" class="grid__col">
                                                <table style="
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
        width:100%;
    " align="left">
                                                    <tr class="row_section" style="" bgcolor="#FFFFFF">
                                                        <td width="20" bgcolor="#FFFFFF" align="left"></td>
                                                        <td class="" style="text-align:left;background-color:#FFFFFF;" align="left">
                                                            <table style="
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
        width:100%;
    " class="" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF" align="left">

                                                                <tr style="" bgcolor="white">
                                                                    <td class="" style="
        padding: 0;
     text-align:left;" align="left" bgcolor="white" width="100%" height="">

                                                                        <table cellpadding="0" cellspacing="0" border="0" class="no_text_resize" width="100%" style="border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                                                                            <tr>
                                                                                <td colspan="3">

                                                                                    <h2 style="
        padding: 0;
        margin: 12px 0 0 0;

        font-size: 23px;
        line-height: 32px;
        font-weight: normal;
        font-weight: 500;
        color: #1E0A3C;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
    margin-top: 0;" class="">

                                                                                        Order Summary

                                                                                    </h2>

                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:6px;font-size:6px;background-color:#FFFFFF;" width="600" height="6">
                                                                                    <table style="mso-line-height-rule:exactly;line-height:6px;font-size:6px;height:6px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="6">

                                                                                        <tr>
                                                                                            <td style="mso-line-height-rule:exactly;line-height:12px;font-size:12px;height:12px;" height="12" bgcolor=""></td>
                                                                                        </tr>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td colspan="3">

                                                                                            <span style="
        font-weight: normal;
        margin: 4px 0;

        font-size: 15px;
        line-height: 21px;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#6F7287" class="">

                    Ticket

        <a style="
        text-decoration:none;color:#3F60E7;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
    font-weight:normal; " href="#" class="">#'.$details['id'].'</a> - '
            .date('F j, Y, H:i:s').'

    </span>

                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td colspan="3">
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:8px;font-size:8px;background-color:#FFFFFF;" width="600" height="8">
                                                                                    <table style="mso-line-height-rule:exactly;line-height:8px;font-size:8px;height:8px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="8">

                                                                                        <tr>
                                                                                            <td style="mso-line-height-rule:exactly;line-height:16px;font-size:16px;height:16px;" height="16" bgcolor=""></td>
                                                                                        </tr>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td colspan="3">
                                                                                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                                                                        <tbody>
                                                                                        <tr>
                                                                                            <td width="25%" valign="top">

                                                                                                            <span style="
        font-weight: normal;
        margin: 4px 0;

        font-size: 15px;
        line-height: 21px;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#6F7287;font-weight:normal;" class="">

                                        '.ucwords($details['fullname']).'

    </span>

                                                                                            </td>
                                                                                            <td width="50%" valign="top">

                                                                                                            <span style="
        font-weight: normal;
        margin: 4px 0 0 8px;

        font-size: 15px;
        padding-left: 10px;
        line-height: 21px;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#6F7287;font-weight:normal;" class="">

                                        Seat #

    </span>

                                                                                                <span style="
        font-weight: normal;
        margin: 4px 0;

        font-size: 15px;
        line-height: 21px;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#1E0A3C;font-weight:600;" class=""><b style="font-weight: normal;
        margin: 4px 0;

        font-size: 15px;
        line-height: 21px;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#1E0A3C;font-weight:600;">

                                        '.$details['seat_no'].'

    </b></span>

                                                                                            </td>
                                                                                            <td width="20%" align="right" valign="top" style="
        text-align: right;
    ">

                                                                                                            <span style="
        font-weight: normal;
        margin: 4px 0;

        font-size: 15px;
        line-height: 21px;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#6F7287;font-weight:normal;" class="">

                                        3000

    </span>

                                                                                            </td>
                                                                                        </tr>

                                                                                        <tr>
                                                                                            <td style="mso-line-height-rule:exactly;line-height:6px;font-size:6px;background-color:#FFFFFF;" width="600" height="6">
                                                                                                <table style="mso-line-height-rule:exactly;line-height:6px;font-size:6px;height:6px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="6">

                                                                                                    <tr>
                                                                                                        <td style="mso-line-height-rule:exactly;line-height:12px;font-size:12px;height:12px;" height="12" bgcolor=""></td>
                                                                                    </tr>
                                                                                    </table>
                                                                                        </td>
                                                                                        </tr>

                                                                                        </tbody>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:4px;font-size:4px;background-color:#FFFFFF;" width="600" height="4">
                                                                                    <table style="mso-line-height-rule:exactly;line-height:4px;font-size:4px;height:4px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="4">

                                                                                        <tr>
                                                                                            <td style="mso-line-height-rule:exactly;line-height:8px;font-size:8px;height:8px;" height="8" bgcolor=""></td>
                                                                                        </tr>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>

                                                                        </table>

                                                              

                                                                <tr style="" bgcolor="white">
                                                                    <td class="" style="
        padding: 0;font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#1E0A3C;font-weight:600;
     text-align:left;" align="left" bgcolor="white" width="100%" height="26">

                                                                                        <span style="
        font-weight: normal;
        margin: 4px 0;

        font-size: 13px;
        line-height: 22px;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#990000;font-weight:600;" class="no_text_resize">

    </span>

                                                                    </td>
                                                                </tr>


                                                                </td>
                                                                </tr>

                                                            </table>
                                                        </td>
                                                        <td width="20" bgcolor="#FFFFFF"></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;background-color:#FFFFFF;
    border-top-left-radius:0;border-top-right-radius:0;border-bottom-right-radius:4px;border-bottom-left-radius:4px;
    " width="600" height="18">
                                                <table style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="18">
                                                    <tr bgcolor="#FFFFFF">
                                                        <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;" height="18"></td>
                                                    </tr>

                                                    <tr>
                                                        <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;" height="18" bgcolor=""></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="mso-line-height-rule:exactly;line-height:4px;font-size:4px;background-color:#EEEDF2;" width="600" height="4">
                                                <table style="mso-line-height-rule:exactly;line-height:4px;font-size:4px;height:4px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="4">

                                                    <tr>
                                                        <td style="mso-line-height-rule:exactly;line-height:8px;font-size:8px;height:8px;" height="8" bgcolor=""></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;background-color:#EEEDF2;" width="600" height="18">
                                                <table style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="18">

                                                    <tr>
                                                        <td style="mso-line-height-rule:exactly;line-height:36px;font-size:36px;height:36px;" height="36" bgcolor=""></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <script type="application/ld+json">
                                            { "@context": "http://schema.org", "@type": "EventReservation", "reservationNumber": "973030087", "reservationStatus": "http://schema.org/Confirmed", "modifyReservationUrl": "https://www.eventbrite.com/mytickets/973030087?utm_campaign=order_confirm&amp;utm_medium=email&amp;ref=eemailordconf&amp;utm_source=eb_email&amp;utm_term=googlenow", "underName": { "@type": "Person", "name": "James Muriithi" }, "reservationFor": { "@type": "Event", "name": "ALC MEETUP 1.0 [ Mombasa ]", "startDate": "2019-06-29T08:00:00+03:00", "endDate": "2019-06-29T14:00:00+03:00", "location": { "@type": "Place", "name": "Swahili Box", "address": { "@type": "PostalAddress", "streetAddress": "Sir Mbarak Hinawy Road", "addressLocality": "Mombasa", "addressRegion": "Mombasa County", "postalCode": "", "addressCountry": "KE" } } } }
                                        </script>

                                        <!-- Footer Section-->

                                        <tr bgcolor="#EEEDF2">
                                            <td style="background-color:#EEEDF2;" bgcolor="#EEEDF2" class="grid__col">
                                                <table style="
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
        width:100%;
    " align="center">
                                                    <tr class="row_section" style="" bgcolor="#EEEDF2">
                                                        <td width="30" bgcolor="#EEEDF2" align="center"></td>
                                                        <td class="" style="text-align:center;background-color:#EEEDF2;" align="center">
                                                            <table style="
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
        width:100%;
    color:#1E0A3C;" class="" cellspacing="0" cellpadding="0" bgcolor="#EEEDF2" align="center">

                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="">

                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="">

                                                                       

                                                                    </td>
                                                                </tr>

                                                                </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="mso-line-height-rule:exactly;line-height:12px;font-size:12px;background-color:#EEEDF2;" width="600" height="12">
                                                                        <table style="mso-line-height-rule:exactly;line-height:12px;font-size:12px;height:12px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="12">

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:24px;font-size:24px;height:24px;" height="24" bgcolor=""></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>

                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="">

                                                                                <span class="social-logo-container" style="
        padding:0;
        display:inline-block;
        height:auto;
        margin:0;
        width:40px;
        text-align:center;
    ">
        <a href="#" target="_blank" aria-label="Eventbrite&#39;s Twitter">
            <img src="https://cdn.evbstatic.com/s3-build/perm_001/c07442/django/images/emails_2018_rebrand/TW-icon-purple@2x.png"
                 class="footer-social-logo__image"
                 style="
        height:24px;
        padding:0;
        width:24px;
    "
                 height="24" width="24" alt="twitter" title="twitter" border="0" />
        </a>
    </span>

                                                                        <span class="social-logo-container" style="
        padding:0;
        display:inline-block;
        height:auto;
        margin:0;
        width:40px;
        text-align:center;
    ">
        <a href="#" target="_blank" aria-label="Eventbrite Facebook">
            <img src="https://cdn.evbstatic.com/s3-build/perm_001/ac2bf4/django/images/emails_2018_rebrand/FB-icon-purple@2x.png"
                 class="footer-social-logo__image"
                 style="
        height:24px;
        padding:0;
        width:24px;
    "
                 height="24" width="24" alt="facebook" title="facebook" border="0" />
        </a>
    </span>

                                                                        <span class="social-logo-container" style="
        padding:0;
        display:inline-block;
        height:auto;
        margin:0;
        width:40px;
        text-align:center;
    ">
        <a href="#" target="_blank" aria-label="Eventbrite&#39;s Instagram">
            <img src="https://cdn.evbstatic.com/s3-build/perm_001/009b0f/django/images/emails_2018_rebrand/IG-icon-purple@2x.png"
                 class="footer-social-logo__image"
                 style="
        height:24px;
        padding:0;
        width:24px;
    "
                 height="24" width="24" alt="instagram" title="instagram" border="0" />
        </a>
    </span>

                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="mso-line-height-rule:exactly;line-height:10px;font-size:10px;background-color:#EEEDF2;" width="600" height="10">
                                                                        <table style="mso-line-height-rule:exactly;line-height:10px;font-size:10px;height:10px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="10">

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:20px;font-size:20px;height:20px;" height="20" bgcolor=""></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>

                                                                


                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="footer-content" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="24">

                                                                                <span style="
        font-weight: normal;
        margin: 4px 0;

        font-size: 12px;
        line-height: 18px;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#4B4D63;font-weight:normal;" class="">

            Copyright &copy; 2019 BUS. All rights reserved.

    </span>

                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;background-color:#EEEDF2;" width="600" height="18">
                                                                        <table style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="18">

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:36px;font-size:36px;height:36px;" height="36" bgcolor=""></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>

                                                                <img src="https://www.eventbrite.com/emails/action/?recipient=muriithijames556%40gmail.com&amp;type_id=65&amp;type=open&amp;send_id=2019-06-25&amp;list_id=9" alt="" width="1" height="1" />

                                                            </table>
                                                        </td>
                                                        <td width="30" bgcolor="#EEEDF2"></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>

</html>';
        return $message;
    }

    /**
     * @description
     * @param $seat_no
     * @return string
     */
    public function generateMessage($seat_no):string
    {
        $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Turn off iOS phone number autodetect -->
    <meta name="format-detection" content="telephone=no" />
    <meta name="format-detection" content="address=no" />

    <!--[if mso]>
    <style>
        * { font-family: sans-serif !important; }
    </style>
    <![endif]-->
    <style type="text/css">
        /*
         * Styles for all the emails
         */
        /* Normalizing */

        #outlook a {
            padding: 0;
        }

        * {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }

        html,
        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            font-weight: normal;
            width: 100% !important;
            height: 100% !important;
            margin: 0;
            padding: 0;
        }

        img {
            border: 0;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
            height: auto;
            line-height: 100%;
            display: inline-block;
        }

        a img {
            border: none;
            display: block;
        }

        h1 {
            color: #1E0A3C !important;
        }

        h2,
        h3,
        h4,
        h5,
        h6 {
            color: #1E0A3C !important;
        }

        h1 a,
        h2 a,
        h3 a,
        h4 a,
        h5 a,
        h6 a {
            color: #3F60E7 !important;
        }

        h1 a:active,
        h2 a:active,
        h3 a:active,
        h4 a:active,
        h5 a:active,
        h6 a:active {
            color: #3F60E7 !important;
        }

        h1 a:visited,
        h2 a:visited,
        h3 a:visited,
        h4 a:visited,
        h5 a:visited,
        h6 a:visited {
            color: #3F60E7 !important;
        }

        table {
            border-collapse: collapse !important;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        table td {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        table {
            border-spacing: 0;
            border: 0;
            padding: 0;
            table-layout: fixed;
            margin: 0;
        }

        .no_text_resize {
            -moz-text-size-adjust: none;
            -webkit-text-size-adjust: none;
            -ms-text-size-adjust: none;
            text-size-adjust: none;
        }

        a[href^="x-apple-data-detectors:"] {
            color: inherit !important;
            text-decoration: none !important;
            font-size: inherit !important;
            font-family: inherit !important;
            font-weight: inherit !important;
            line-height: inherit !important;
        }

        td[class="grid__col"] {
            padding-left: 60px !important;
            padding-right: 60px !important;
        }

        .grid__col {
            padding-left: 60px !important;
            padding-right: 60px !important;
        }
        /* Footer */

        .footer-content.bottom-section {
            padding: 0px 32px;
        }

        .content .align-center,
        .footer-content .align-center {
            text-align: center;
        }

        .footer-content {
            color: #B7B6C0 !important;
        }

        .footer-content a {
            color: #f05537 !important;
            text-decoration: none;
        }
        /* Social */

        .social-logo-container {
            display: inline-block;
            height: auto;
            margin: 0px;
        }

        .content {
            width: 600px;
        }
        /* Media Queries */

        @media all and (max-width: 600px) {
            .content {
                /* slight space on each side */
                width: 97% !important;
            }
            *[class="gmail-fix"] {
                display: none !important;
            }
            td[class="grid__col"] {
                padding-left: 0px !important;
                padding-right: 0px !important;
                max-width: 90% !important;
            }
            .grid__col {
                padding-left: 0px !important;
                padding-right: 0px !important;
                max-width: 90% !important;
            }
            .social-logo-container {
                display: inline-block !important;
            }
            .btn {
                font-size: 15px !important;
                padding: 12px 28px 14px !important;
                margin-bottom: 18px;
            }
            .hide {
                display: none;
            }
        }
    </style>

    <!--[if (mso)|(IE)]>
    <xml:namespace ns="urn:schemas-microsoft-com:vml" prefix="v" />
    <style>
        v\: * { behavior:url(#default#VML); display:inline-block }
        .container-table {
            width: 100%;
        }
    </style>
    <!<![endif]-->
    <!--[if (gte mso 9)|(IE)]>
    <style>
        .hide {
            display: none;
        }
    </style>
    <![endif]-->

</head>

<!-- Global container with background styles. Gmail converts BODY to DIV so we lose properties like BGCOLOR. -->

<body style="
        padding:0px !important;
        margin:0px !important;
    ">
<div class="hide" style="letter-spacing:596px;line-height:0;mso-hide:all"></div>
<!-- Outermost Wrapper Table -->
<table style="min-width:100%;" width="100%" bgcolor="#EEEDF2">
    <tr>
        <td>
            <table style="
        margin-left:auto;
        margin-right:auto;

        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    " cellpadding="0" cellspacing="0" border="0" class="container-table">
                <tr>
                    <!-- Centered 600px Email Table -->
                    <td style="
        margin-left:auto;
        margin-right:auto;
    " align="center" bgcolor="#EEEDF2">
                        <table class="content" style="border: none;">
                            <tr>
                                <td align="center" bgcolor="#EEEDF2">
                                    <table cellpadding="0" cellspacing="0" border="0" bgcolor="#EEEDF2">
                                        <tbody>

                                        <!-- Body Section -->

                                        <tr>
                                            <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;background-color:#EEEDF2;" width="600" height="18">
                                                <table style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="18">

                                                    <tr>
                                                        <td style="mso-line-height-rule:exactly;line-height:36px;font-size:36px;height:36px;" height="36" bgcolor=""></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr bgcolor="#EEEDF2">
                                            <td style="background-color:#EEEDF2;" bgcolor="#EEEDF2" class="grid__col">
                                                <table style="
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
        width:100%;
    " align="center">
                                                    <tr class="row_section" style="" bgcolor="#EEEDF2">
                                                        <td width="30" bgcolor="#EEEDF2" align="center"></td>
                                                        <td class="" style="text-align:center;background-color:#EEEDF2;" align="center">
                                                            <table style="
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
        width:100%;
    " class="" cellspacing="0" cellpadding="0" bgcolor="#EEEDF2" align="center">

                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="">

                                                                        <div style="
        width: 140px;
        height: 25px;
        margin: 0 auto;
        text-align: center;
    ">

                                                                            <img src=\'https://examinationcomplaint.theschemaqhigh.co.ke/HCI/bus.png\' title=\'\' alt=\'Logo\' style=\'\' border="0" width=\'140\' height=\'25\' class="" />

                                                                        </div>

                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;background-color:#EEEDF2;" width="600" height="18">
                                                                        <table style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="18">

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:36px;font-size:36px;height:36px;" height="36" bgcolor=""></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>

                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="">

                                                                        <h1 style="
        padding: 0;
        margin: 12px 0 0 0;

        font-size: 25px;
        line-height: 42px;
        font-weight: bold;
        font-weight: 800;
        letter-spacing: -0.2px;
        color: #1E0A3C;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
    color: #1E0A3C; margin-top:0;" class="h1-header">

                                                                            '.ucfirst(explode(' ',$this->getName())[0]).', <br />your seat has been reserved

                                                                        </h1>

                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;background-color:#EEEDF2;" width="600" height="18">
                                                                        <table style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="18">

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:36px;font-size:36px;height:36px;" height="36" bgcolor=""></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>

                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="">

                                                                        <img src=\'https://theschemaqhigh.co.ke/examinationcomplaint/HCI/booked.png\' title=\'\' alt=\'Eventbrite\' style=\'\' border="0" width=\'115\' height=\'115\' class="" />

                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="mso-line-height-rule:exactly;line-height:8px;font-size:8px;background-color:#EEEDF2;" width="600" height="8">
                                                                        <table style="mso-line-height-rule:exactly;line-height:8px;font-size:8px;height:8px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="8">

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:16px;font-size:16px;height:16px;" height="16" bgcolor=""></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>

                                                            </table>
                                                        </td>
                                                        <td width="30" bgcolor="#EEEDF2"></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr bgcolor="#EEEDF2">
                                            <td style="background-color:#EEEDF2;" bgcolor="#EEEDF2" class="grid__col">
                                                <table style="
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
        width:100%;
    " align="center">
                                                    <tr class="row_section" style="" bgcolor="#EEEDF2">
                                                        <td width="30" bgcolor="#EEEDF2" align="center"></td>
                                                        <td class="" style="text-align:center;background-color:#EEEDF2;" align="center">
                                                            <table style="
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
        width:100%;
    color:#1E0A3C;" class="" cellspacing="0" cellpadding="0" bgcolor="#EEEDF2" align="center">

                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="">


                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="mso-line-height-rule:exactly;line-height:12px;font-size:12px;background-color:#EEEDF2;" width="600" height="12">
                                                                        <table style="mso-line-height-rule:exactly;line-height:12px;font-size:12px;height:12px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="12">

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:24px;font-size:24px;height:24px;" height="24" bgcolor=""></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>

                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="">

                                                                        <!--[if (mso)|(ie)]>
                                                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="https://www.eventbrite.com/eventbriteapp?utm_campaign=order_confirm&amp;utm_medium=email&amp;ref=eemailordconf&amp;app_cta_src=order_conf_email&amp;utm_source=eb_email&amp;utm_term=downloadapp" style="height:40px;v-text-anchor:middle;width:225px;" arcsize="10%" strokeweight="1px" strokecolor="#f05537" fillcolor="#f05537">
                                                            <w:anchorlock/>
                                                            <center style="color:#FFFFFF;font-family:sans-serif;font-size:16px;font-weight:normal;">
                                                                Get the app
                                                            </center>
                                                        </v:roundrect>
                                                        <![endif]-->
                                                                        <!--[if !((mso)|(ie))]><!-- -->
                                                                        <div style="text-align:center;
        font-family: &#39;Helvetica Neue&#39;, Helvetica, Arial, sans-serif;

        padding: 0;
    ">
                                                                            <a 
                      href="https://www.bus.theschemaqhigh.co.ke/print-ticket.php?bid='.$this->getBusId().'&seat='. $seat_no .'" target="_blank" style="
                background-color:#f05537;
                color:#FFFFFF;
                border-color:#f05537;

        display: inline-block;
        line-height: 22px;
        font-weight: normal;
        font-weight: 500;
        font-size: 15px;
        text-align: center;
        text-decoration: none;
        padding: 8px 28px 12px;
        border-radius: 4px;
        border-style: solid;
        border-width: 2px;

            " class="btn ">
                                                                                Print Ticket
                                                                            </a>
                                                                        </div>
                                                                        <!--<![endif]-->

                                                                    </td>
                                                                </tr>

                                                            </table>
                                                        </td>
                                                        <td width="30" bgcolor="#EEEDF2"></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;background-color:#EEEDF2;" width="600" height="18">
                                                <table style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="18">

                                                    <tr>
                                                        <td style="mso-line-height-rule:exactly;line-height:36px;font-size:36px;height:36px;" height="36" bgcolor=""></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;background-color:#FFFFFF;
    border-top-left-radius:4px;border-top-right-radius:4px;border-bottom-right-radius:0;border-bottom-left-radius:0;
    " width="600" height="18">
                                                <table style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="18">

                                                    <tr>
                                                        <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;" height="18" bgcolor=""></td>
                                                    </tr>
                                                    <tr bgcolor="#FFFFFF">
                                                        <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;" height="18"></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
<!-- ------------------------------------------------------------------------ -->
<!------------------------------------------------------------------------------------ -->
                                        <tr bgcolor="#FFFFFF">
                                            <td style="background-color:#FFFFFF;" bgcolor="#FFFFFF" class="grid__col">
                                                <table style="
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
        width:100%;
    " align="left">
                                                    <tr class="row_section" style="" bgcolor="#FFFFFF">
                                                        <td width="20" bgcolor="#FFFFFF" align="left"></td>
                                                        <td class="" style="text-align:left;background-color:#FFFFFF;" align="left">
                                                            <table style="
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
        width:100%;
    " class="" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF" align="left">

                                                                <tr style="" bgcolor="white">
                                                                    <td class="" style="
        padding: 0;
     text-align:left;" align="left" bgcolor="white" width="100%" height="">

                                                                        <table cellpadding="0" cellspacing="0" border="0" class="no_text_resize" width="100%" style="border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                                                                            <tr>
                                                                                <td colspan="3">

                                                                                    <h2 style="
        padding: 0;
        margin: 12px 0 0 0;

        font-size: 23px;
        line-height: 32px;
        font-weight: normal;
        font-weight: 500;
        color: #1E0A3C;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
    margin-top: 0;" class="">

                                                                                        Order Summary

                                                                                    </h2>

                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:6px;font-size:6px;background-color:#FFFFFF;" width="600" height="6">
                                                                                    <table style="mso-line-height-rule:exactly;line-height:6px;font-size:6px;height:6px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="6">

                                                                                        <tr>
                                                                                            <td style="mso-line-height-rule:exactly;line-height:12px;font-size:12px;height:12px;" height="12" bgcolor=""></td>
                                                                                        </tr>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td colspan="3">

                                                                                            <span style="
        font-weight: normal;
        margin: 4px 0;

        font-size: 15px;
        line-height: 21px;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#6F7287" class="">

                    Ticket

        <a style="
        text-decoration:none;color:#3F60E7;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
    font-weight:normal; " href="#" class="">#'.$this->getReceiptId($seat_no).'</a> - '
            .date('F j, Y, H:i:s').'

    </span>

                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td colspan="3">
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:8px;font-size:8px;background-color:#FFFFFF;" width="600" height="8">
                                                                                    <table style="mso-line-height-rule:exactly;line-height:8px;font-size:8px;height:8px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="8">

                                                                                        <tr>
                                                                                            <td style="mso-line-height-rule:exactly;line-height:16px;font-size:16px;height:16px;" height="16" bgcolor=""></td>
                                                                                        </tr>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td colspan="3">
                                                                                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                                                                        <tbody>
                                                                                        <tr>
                                                                                            <td width="25%" valign="top">

                                                                                                            <span style="
        font-weight: normal;
        margin: 4px 0;

        font-size: 15px;
        line-height: 21px;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#6F7287;font-weight:normal;" class="">

                                        '.ucwords($this->getName()).'

    </span>

                                                                                            </td>
                                                                                            <td width="50%" valign="top">

                                                                                                            <span style="
        font-weight: normal;
        margin: 4px 0 0 8px;

        font-size: 15px;
        padding-left: 10px;
        line-height: 21px;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#6F7287;font-weight:normal;" class="">

                                        Seat #

    </span>

                                                                                                <span style="
        font-weight: normal;
        margin: 4px 0;

        font-size: 15px;
        line-height: 21px;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#1E0A3C;font-weight:600;" class=""><b style="font-weight: normal;
        margin: 4px 0;

        font-size: 15px;
        line-height: 21px;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#1E0A3C;font-weight:600;">

                                        '.$seat_no.'

    </b></span>

                                                                                            </td>
                                                                                            <td width="20%" align="right" valign="top" style="
        text-align: right;
    ">

                                                                                                            <span style="
        font-weight: normal;
        margin: 4px 0;

        font-size: 15px;
        line-height: 21px;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#6F7287;font-weight:normal;" class="">

                                        3000

    </span>

                                                                                            </td>
                                                                                        </tr>

                                                                                        <tr>
                                                                                            <td style="mso-line-height-rule:exactly;line-height:6px;font-size:6px;background-color:#FFFFFF;" width="600" height="6">
                                                                                                <table style="mso-line-height-rule:exactly;line-height:6px;font-size:6px;height:6px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="6">

                                                                                                    <tr>
                                                                                                        <td style="mso-line-height-rule:exactly;line-height:12px;font-size:12px;height:12px;" height="12" bgcolor=""></td>
                                                                                    </tr>
                                                                                    </table>
                                                                                        </td>
                                                                                        </tr>

                                                                                        </tbody>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:4px;font-size:4px;background-color:#FFFFFF;" width="600" height="4">
                                                                                    <table style="mso-line-height-rule:exactly;line-height:4px;font-size:4px;height:4px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="4">

                                                                                        <tr>
                                                                                            <td style="mso-line-height-rule:exactly;line-height:8px;font-size:8px;height:8px;" height="8" bgcolor=""></td>
                                                                                        </tr>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>

                                                                        </table>

                                                              

                                                                <tr style="" bgcolor="white">
                                                                    <td class="" style="
        padding: 0;font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#1E0A3C;font-weight:600;
     text-align:left;" align="left" bgcolor="white" width="100%" height="26">

                                                                                        <span style="
        font-weight: normal;
        margin: 4px 0;

        font-size: 13px;
        line-height: 22px;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#990000;font-weight:600;" class="no_text_resize">
                                Please send your money to <b>0717456520</b> Peter Lemaron.
                                 A Printable PDF ticket will be sent to you upon payment completion.<br/>Safe Journey

    </span>

                                                                    </td>
                                                                </tr>


                                                                </td>
                                                                </tr>

                                                            </table>
                                                        </td>
                                                        <td width="20" bgcolor="#FFFFFF"></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;background-color:#FFFFFF;
    border-top-left-radius:0;border-top-right-radius:0;border-bottom-right-radius:4px;border-bottom-left-radius:4px;
    " width="600" height="18">
                                                <table style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="18">
                                                    <tr bgcolor="#FFFFFF">
                                                        <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;" height="18"></td>
                                                    </tr>

                                                    <tr>
                                                        <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;" height="18" bgcolor=""></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="mso-line-height-rule:exactly;line-height:4px;font-size:4px;background-color:#EEEDF2;" width="600" height="4">
                                                <table style="mso-line-height-rule:exactly;line-height:4px;font-size:4px;height:4px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="4">

                                                    <tr>
                                                        <td style="mso-line-height-rule:exactly;line-height:8px;font-size:8px;height:8px;" height="8" bgcolor=""></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;background-color:#EEEDF2;" width="600" height="18">
                                                <table style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="18">

                                                    <tr>
                                                        <td style="mso-line-height-rule:exactly;line-height:36px;font-size:36px;height:36px;" height="36" bgcolor=""></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <script type="application/ld+json">
                                            { "@context": "http://schema.org", "@type": "EventReservation", "reservationNumber": "973030087", "reservationStatus": "http://schema.org/Confirmed", "modifyReservationUrl": "https://www.eventbrite.com/mytickets/973030087?utm_campaign=order_confirm&amp;utm_medium=email&amp;ref=eemailordconf&amp;utm_source=eb_email&amp;utm_term=googlenow", "underName": { "@type": "Person", "name": "James Muriithi" }, "reservationFor": { "@type": "Event", "name": "ALC MEETUP 1.0 [ Mombasa ]", "startDate": "2019-06-29T08:00:00+03:00", "endDate": "2019-06-29T14:00:00+03:00", "location": { "@type": "Place", "name": "Swahili Box", "address": { "@type": "PostalAddress", "streetAddress": "Sir Mbarak Hinawy Road", "addressLocality": "Mombasa", "addressRegion": "Mombasa County", "postalCode": "", "addressCountry": "KE" } } } }
                                        </script>

                                        <!-- Footer Section-->

                                        <tr bgcolor="#EEEDF2">
                                            <td style="background-color:#EEEDF2;" bgcolor="#EEEDF2" class="grid__col">
                                                <table style="
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
        width:100%;
    " align="center">
                                                    <tr class="row_section" style="" bgcolor="#EEEDF2">
                                                        <td width="30" bgcolor="#EEEDF2" align="center"></td>
                                                        <td class="" style="text-align:center;background-color:#EEEDF2;" align="center">
                                                            <table style="
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
        width:100%;
    color:#1E0A3C;" class="" cellspacing="0" cellpadding="0" bgcolor="#EEEDF2" align="center">

                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="">

                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="">

                                                                       

                                                                    </td>
                                                                </tr>

                                                                </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="mso-line-height-rule:exactly;line-height:12px;font-size:12px;background-color:#EEEDF2;" width="600" height="12">
                                                                        <table style="mso-line-height-rule:exactly;line-height:12px;font-size:12px;height:12px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="12">

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:24px;font-size:24px;height:24px;" height="24" bgcolor=""></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>

                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="">

                                                                                <span class="social-logo-container" style="
        padding:0;
        display:inline-block;
        height:auto;
        margin:0;
        width:40px;
        text-align:center;
    ">
        <a href="#" target="_blank" aria-label="Eventbrite&#39;s Twitter">
            <img src="https://cdn.evbstatic.com/s3-build/perm_001/c07442/django/images/emails_2018_rebrand/TW-icon-purple@2x.png"
                 class="footer-social-logo__image"
                 style="
        height:24px;
        padding:0;
        width:24px;
    "
                 height="24" width="24" alt="twitter" title="twitter" border="0" />
        </a>
    </span>

                                                                        <span class="social-logo-container" style="
        padding:0;
        display:inline-block;
        height:auto;
        margin:0;
        width:40px;
        text-align:center;
    ">
        <a href="#" target="_blank" aria-label="Eventbrite Facebook">
            <img src="https://cdn.evbstatic.com/s3-build/perm_001/ac2bf4/django/images/emails_2018_rebrand/FB-icon-purple@2x.png"
                 class="footer-social-logo__image"
                 style="
        height:24px;
        padding:0;
        width:24px;
    "
                 height="24" width="24" alt="facebook" title="facebook" border="0" />
        </a>
    </span>

                                                                        <span class="social-logo-container" style="
        padding:0;
        display:inline-block;
        height:auto;
        margin:0;
        width:40px;
        text-align:center;
    ">
        <a href="#" target="_blank" aria-label="Eventbrite&#39;s Instagram">
            <img src="https://cdn.evbstatic.com/s3-build/perm_001/009b0f/django/images/emails_2018_rebrand/IG-icon-purple@2x.png"
                 class="footer-social-logo__image"
                 style="
        height:24px;
        padding:0;
        width:24px;
    "
                 height="24" width="24" alt="instagram" title="instagram" border="0" />
        </a>
    </span>

                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="mso-line-height-rule:exactly;line-height:10px;font-size:10px;background-color:#EEEDF2;" width="600" height="10">
                                                                        <table style="mso-line-height-rule:exactly;line-height:10px;font-size:10px;height:10px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="10">

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:20px;font-size:20px;height:20px;" height="20" bgcolor=""></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>

                                                                


                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="footer-content" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="24">

                                                                                <span style="
        font-weight: normal;
        margin: 4px 0;

        font-size: 12px;
        line-height: 18px;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#4B4D63;font-weight:normal;" class="">

            Copyright &copy; 2019 BUS. All rights reserved.

    </span>

                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;background-color:#EEEDF2;" width="600" height="18">
                                                                        <table style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="18">

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:36px;font-size:36px;height:36px;" height="36" bgcolor=""></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>

                                                                <img src="https://www.eventbrite.com/emails/action/?recipient=muriithijames556%40gmail.com&amp;type_id=65&amp;type=open&amp;send_id=2019-06-25&amp;list_id=9" alt="" width="1" height="1" />

                                                            </table>
                                                        </td>
                                                        <td width="30" bgcolor="#EEEDF2"></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>

</html>';
        return $message;
    }

//    for ticket attachment------------------------------------------------------------------------------------public
    public function generateMessageTicket($seat_no):string
    {
        $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Turn off iOS phone number autodetect -->
    <meta name="format-detection" content="telephone=no" />
    <meta name="format-detection" content="address=no" />

    <!--[if mso]>
    <style>
        * { font-family: sans-serif !important; }
    </style>
    <![endif]-->
    <style type="text/css">
        /*
         * Styles for all the emails
         */
        /* Normalizing */

        #outlook a {
            padding: 0;
        }

        * {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }

        html,
        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            font-weight: normal;
            width: 100% !important;
            height: 100% !important;
            margin: 0;
            padding: 0;
        }

        img {
            border: 0;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
            height: auto;
            line-height: 100%;
            display: inline-block;
        }

        a img {
            border: none;
            display: block;
        }

        h1 {
            color: #1E0A3C !important;
        }

        h2,
        h3,
        h4,
        h5,
        h6 {
            color: #1E0A3C !important;
        }

        h1 a,
        h2 a,
        h3 a,
        h4 a,
        h5 a,
        h6 a {
            color: #3F60E7 !important;
        }

        h1 a:active,
        h2 a:active,
        h3 a:active,
        h4 a:active,
        h5 a:active,
        h6 a:active {
            color: #3F60E7 !important;
        }

        h1 a:visited,
        h2 a:visited,
        h3 a:visited,
        h4 a:visited,
        h5 a:visited,
        h6 a:visited {
            color: #3F60E7 !important;
        }

        table {
            border-collapse: collapse !important;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        table td {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        table {
            border-spacing: 0;
            border: 0;
            padding: 0;
            table-layout: fixed;
            margin: 0;
        }

        .no_text_resize {
            -moz-text-size-adjust: none;
            -webkit-text-size-adjust: none;
            -ms-text-size-adjust: none;
            text-size-adjust: none;
        }

        a[href^="x-apple-data-detectors:"] {
            color: inherit !important;
            text-decoration: none !important;
            font-size: inherit !important;
            font-family: inherit !important;
            font-weight: inherit !important;
            line-height: inherit !important;
        }

        td[class="grid__col"] {
            padding-left: 60px !important;
            padding-right: 60px !important;
        }

        .grid__col {
            padding-left: 60px !important;
            padding-right: 60px !important;
        }
        /* Footer */

        .footer-content.bottom-section {
            padding: 0px 32px;
        }

        .content .align-center,
        .footer-content .align-center {
            text-align: center;
        }

        .footer-content {
            color: #B7B6C0 !important;
        }

        .footer-content a {
            color: #f05537 !important;
            text-decoration: none;
        }
        /* Social */

        .social-logo-container {
            display: inline-block;
            height: auto;
            margin: 0px;
        }

        .content {
            width: 600px;
        }
        /* Media Queries */

        @media all and (max-width: 600px) {
            .content {
                /* slight space on each side */
                width: 97% !important;
            }
            *[class="gmail-fix"] {
                display: none !important;
            }
            td[class="grid__col"] {
                padding-left: 0px !important;
                padding-right: 0px !important;
                max-width: 90% !important;
            }
            .grid__col {
                padding-left: 0px !important;
                padding-right: 0px !important;
                max-width: 90% !important;
            }
            .social-logo-container {
                display: inline-block !important;
            }
            .btn {
                font-size: 15px !important;
                padding: 12px 28px 14px !important;
                margin-bottom: 18px;
            }
            .hide {
                display: none;
            }
        }
    </style>

    <!--[if (mso)|(IE)]>
    <xml:namespace ns="urn:schemas-microsoft-com:vml" prefix="v" />
    <style>
        v\: * { behavior:url(#default#VML); display:inline-block }
        .container-table {
            width: 100%;
        }
    </style>
    <!<![endif]-->
    <!--[if (gte mso 9)|(IE)]>
    <style>
        .hide {
            display: none;
        }
    </style>
    <![endif]-->

</head>

<!-- Global container with background styles. Gmail converts BODY to DIV so we lose properties like BGCOLOR. -->

<body style="
        padding:0px !important;
        margin:0px !important;
    ">
<div class="hide" style="letter-spacing:596px;line-height:0;mso-hide:all"></div>
<!-- Outermost Wrapper Table -->
<table style="min-width:100%;" width="100%" bgcolor="#EEEDF2">
    <tr>
        <td>
            <table style="
        margin-left:auto;
        margin-right:auto;

        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    " cellpadding="0" cellspacing="0" border="0" class="container-table">
                <tr>
                    <!-- Centered 600px Email Table -->
                    <td style="
        margin-left:auto;
        margin-right:auto;
    " align="center" bgcolor="#EEEDF2">
                        <table class="content" style="border: none;">
                            <tr>
                                <td align="center" bgcolor="#EEEDF2">
                                    <table cellpadding="0" cellspacing="0" border="0" bgcolor="#EEEDF2">
                                        <tbody>

                                        <!-- Body Section -->

                                        <tr>
                                            <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;background-color:#EEEDF2;" width="600" height="18">
                                                <table style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="18">

                                                    <tr>
                                                        <td style="mso-line-height-rule:exactly;line-height:36px;font-size:36px;height:36px;" height="36" bgcolor=""></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr bgcolor="#EEEDF2">
                                            <td style="background-color:#EEEDF2;" bgcolor="#EEEDF2" class="grid__col">
                                                <table style="
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
        width:100%;
    " align="center">
                                                    <tr class="row_section" style="" bgcolor="#EEEDF2">
                                                        <td width="30" bgcolor="#EEEDF2" align="center"></td>
                                                        <td class="" style="text-align:center;background-color:#EEEDF2;" align="center">
                                                            <table style="
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
        width:100%;
    " class="" cellspacing="0" cellpadding="0" bgcolor="#EEEDF2" align="center">

                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="">

                                                                        <div style="
        width: 140px;
        height: 25px;
        margin: 0 auto;
        text-align: center;
    ">

                                                                            <img src=\'https://examinationcomplaint.theschemaqhigh.co.ke/HCI/bus.png\' title=\'\' alt=\'Logo\' style=\'\' border="0" width=\'140\' height=\'25\' class="" />

                                                                        </div>

                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;background-color:#EEEDF2;" width="600" height="18">
                                                                        <table style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="18">

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:36px;font-size:36px;height:36px;" height="36" bgcolor=""></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>

                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="">

                                                                        <h1 style="
        padding: 0;
        margin: 12px 0 0 0;

        font-size: 25px;
        line-height: 42px;
        font-weight: bold;
        font-weight: 800;
        letter-spacing: -0.2px;
        color: #1E0A3C;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
    color: #1E0A3C; margin-top:0;" class="h1-header">

                                                                            '.ucfirst(explode(' ',$this->getTicketDetails($seat_no)["fullname"])[0]).', <br />your payment was received

                                                                        </h1>

                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;background-color:#EEEDF2;" width="600" height="18">
                                                                        <table style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="18">

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:36px;font-size:36px;height:36px;" height="36" bgcolor=""></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>

                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="">

                                                                        <img src=\'https://theschemaqhigh.co.ke/examinationcomplaint/HCI/booked.png\' title=\'\' alt=\'Eventbrite\' style=\'\' border="0" width=\'115\' height=\'115\' class="" />

                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="mso-line-height-rule:exactly;line-height:8px;font-size:8px;background-color:#EEEDF2;" width="600" height="8">
                                                                        <table style="mso-line-height-rule:exactly;line-height:8px;font-size:8px;height:8px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="8">

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:16px;font-size:16px;height:16px;" height="16" bgcolor=""></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>

                                                            </table>
                                                        </td>
                                                        <td width="30" bgcolor="#EEEDF2"></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr bgcolor="#EEEDF2">
                                            <td style="background-color:#EEEDF2;" bgcolor="#EEEDF2" class="grid__col">
                                                <table style="
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
        width:100%;
    " align="center">
                                                    <tr class="row_section" style="" bgcolor="#EEEDF2">
                                                        <td width="30" bgcolor="#EEEDF2" align="center"></td>
                                                        <td class="" style="text-align:center;background-color:#EEEDF2;" align="center">
                                                            <table style="
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
        width:100%;
    color:#1E0A3C;" class="" cellspacing="0" cellpadding="0" bgcolor="#EEEDF2" align="center">

                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="">


                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="mso-line-height-rule:exactly;line-height:12px;font-size:12px;background-color:#EEEDF2;" width="600" height="12">
                                                                        <table style="mso-line-height-rule:exactly;line-height:12px;font-size:12px;height:12px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="12">

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:24px;font-size:24px;height:24px;" height="24" bgcolor=""></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>

                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="">

                                                                        <!--[if (mso)|(ie)]>
                                                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="https://www.eventbrite.com/eventbriteapp?utm_campaign=order_confirm&amp;utm_medium=email&amp;ref=eemailordconf&amp;app_cta_src=order_conf_email&amp;utm_source=eb_email&amp;utm_term=downloadapp" style="height:40px;v-text-anchor:middle;width:225px;" arcsize="10%" strokeweight="1px" strokecolor="#f05537" fillcolor="#f05537">
                                                            <w:anchorlock/>
                                                            <center style="color:#FFFFFF;font-family:sans-serif;font-size:16px;font-weight:normal;">
                                                                Get the app
                                                            </center>
                                                        </v:roundrect>
                                                        <![endif]-->
                                                                        <!--[if !((mso)|(ie))]><!-- -->
                                                                        <div style="text-align:center;
        font-family: &#39;Helvetica Neue&#39;, Helvetica, Arial, sans-serif;

        padding: 0;
    ">
                                                                            <a 
                      href="https://www.bus.theschemaqhigh.co.ke/print-ticket.php?bid='.$this->getBusId().'&seat='. $seat_no .'" target="_blank" style="
                background-color:#f05537;
                color:#FFFFFF;
                border-color:#f05537;

        display: inline-block;
        line-height: 22px;
        font-weight: normal;
        font-weight: 500;
        font-size: 15px;
        text-align: center;
        text-decoration: none;
        padding: 8px 28px 12px;
        border-radius: 4px;
        border-style: solid;
        border-width: 2px;

            " class="btn ">
                                                                                Print Ticket
                                                                            </a>
                                                                        </div>
                                                                        <!--<![endif]-->

                                                                    </td>
                                                                </tr>

                                                            </table>
                                                        </td>
                                                        <td width="30" bgcolor="#EEEDF2"></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;background-color:#EEEDF2;" width="600" height="18">
                                                <table style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="18">

                                                    <tr>
                                                        <td style="mso-line-height-rule:exactly;line-height:36px;font-size:36px;height:36px;" height="36" bgcolor=""></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;background-color:#FFFFFF;
    border-top-left-radius:4px;border-top-right-radius:4px;border-bottom-right-radius:0;border-bottom-left-radius:0;
    " width="600" height="18">
                                                <table style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="18">

                                                    <tr>
                                                        <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;" height="18" bgcolor=""></td>
                                                    </tr>
                                                    <tr bgcolor="#FFFFFF">
                                                        <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;" height="18"></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
<!-- ------------------------------------------------------------------------ -->
<!------------------------------------------------------------------------------------ -->
                                        <tr bgcolor="#FFFFFF">
                                            <td style="background-color:#FFFFFF;" bgcolor="#FFFFFF" class="grid__col">
                                                <table style="
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
        width:100%;
    " align="left">
                                                    <tr class="row_section" style="" bgcolor="#FFFFFF">
                                                        <td width="20" bgcolor="#FFFFFF" align="left"></td>
                                                        <td class="" style="text-align:left;background-color:#FFFFFF;" align="left">
                                                            <table style="
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
        width:100%;
    " class="" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF" align="left">

                                                                <tr style="" bgcolor="white">
                                                                    <td class="" style="
        padding: 0;
     text-align:left;" align="left" bgcolor="white" width="100%" height="">

                                                                        <table cellpadding="0" cellspacing="0" border="0" class="no_text_resize" width="100%" style="border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                                                                            <tr>
                                                                                <td colspan="3">

                                                                                    <h2 style="
        padding: 0;
        margin: 12px 0 0 0;

        font-size: 23px;
        line-height: 32px;
        font-weight: normal;
        font-weight: 500;
        color: #1E0A3C;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
    margin-top: 0;" class="">

                                                                                        Order Summary

                                                                                    </h2>

                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:6px;font-size:6px;background-color:#FFFFFF;" width="600" height="6">
                                                                                    <table style="mso-line-height-rule:exactly;line-height:6px;font-size:6px;height:6px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="6">

                                                                                        <tr>
                                                                                            <td style="mso-line-height-rule:exactly;line-height:12px;font-size:12px;height:12px;" height="12" bgcolor=""></td>
                                                                                        </tr>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td colspan="3">

                                                                                            <span style="
        font-weight: normal;
        margin: 4px 0;

        font-size: 15px;
        line-height: 21px;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#6F7287" class="">

                    Ticket

        <a style="
        text-decoration:none;color:#3F60E7;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
    font-weight:normal; " href="#" class="">#'.$this->getReceiptId($seat_no).'</a> - '
            .date('F j, Y, H:i:s').'

    </span>

                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td colspan="3">
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:8px;font-size:8px;background-color:#FFFFFF;" width="600" height="8">
                                                                                    <table style="mso-line-height-rule:exactly;line-height:8px;font-size:8px;height:8px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="8">

                                                                                        <tr>
                                                                                            <td style="mso-line-height-rule:exactly;line-height:16px;font-size:16px;height:16px;" height="16" bgcolor=""></td>
                                                                                        </tr>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td colspan="3">
                                                                                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                                                                        <tbody>
                                                                                        <tr>
                                                                                            <td width="25%" valign="top">

                                                                                                            <span style="
        font-weight: normal;
        margin: 4px 0;

        font-size: 15px;
        line-height: 21px;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#6F7287;font-weight:normal;" class="">

                                        '.ucwords($this->getTicketDetails($seat_no)["fullname"]).'

    </span>

                                                                                            </td>
                                                                                            <td width="50%" valign="top">

                                                                                                            <span style="
        font-weight: normal;
        margin: 4px 0 0 8px;

        font-size: 15px;
        padding-left: 10px;
        line-height: 21px;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#6F7287;font-weight:normal;" class="">

                                        Seat #

    </span>

                                                                                                <span style="
        font-weight: normal;
        margin: 4px 0;

        font-size: 15px;
        line-height: 21px;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#1E0A3C;font-weight:600;" class=""><b style="font-weight: normal;
        margin: 4px 0;

        font-size: 15px;
        line-height: 21px;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#1E0A3C;font-weight:600;">

                                        '.$seat_no.'

    </b></span>

                                                                                            </td>
                                                                                            <td width="20%" align="right" valign="top" style="
        text-align: right;
    ">

                                                                                                            <span style="
        font-weight: normal;
        margin: 4px 0;

        font-size: 15px;
        line-height: 21px;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#6F7287;font-weight:normal;" class="">

                                        3000

    </span>

                                                                                            </td>
                                                                                        </tr>

                                                                                        <tr>
                                                                                            <td style="mso-line-height-rule:exactly;line-height:6px;font-size:6px;background-color:#FFFFFF;" width="600" height="6">
                                                                                                <table style="mso-line-height-rule:exactly;line-height:6px;font-size:6px;height:6px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="6">

                                                                                                    <tr>
                                                                                                        <td style="mso-line-height-rule:exactly;line-height:12px;font-size:12px;height:12px;" height="12" bgcolor=""></td>
                                                                                    </tr>
                                                                                    </table>
                                                                                        </td>
                                                                                        </tr>

                                                                                        </tbody>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:4px;font-size:4px;background-color:#FFFFFF;" width="600" height="4">
                                                                                    <table style="mso-line-height-rule:exactly;line-height:4px;font-size:4px;height:4px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="4">

                                                                                        <tr>
                                                                                            <td style="mso-line-height-rule:exactly;line-height:8px;font-size:8px;height:8px;" height="8" bgcolor=""></td>
                                                                                        </tr>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>

                                                                        </table>

                                                              

                                                                <tr style="" bgcolor="white">
                                                                    <td class="" style="
        padding: 0;font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#1E0A3C;font-weight:600;
     text-align:left;" align="left" bgcolor="white" width="100%" height="26">

                                                                                        <span style="
        font-weight: normal;
        margin: 4px 0;

        font-size: 13px;
        line-height: 22px;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#990000;font-weight:600;" class="no_text_resize">
                                Attached below is your ticket to be presented on the day of travel.<br/>Safe Journey...

    </span>

                                                                    </td>
                                                                </tr>


                                                                </td>
                                                                </tr>

                                                            </table>
                                                        </td>
                                                        <td width="20" bgcolor="#FFFFFF"></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;background-color:#FFFFFF;
    border-top-left-radius:0;border-top-right-radius:0;border-bottom-right-radius:4px;border-bottom-left-radius:4px;
    " width="600" height="18">
                                                <table style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="18">
                                                    <tr bgcolor="#FFFFFF">
                                                        <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;" height="18"></td>
                                                    </tr>

                                                    <tr>
                                                        <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;" height="18" bgcolor=""></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="mso-line-height-rule:exactly;line-height:4px;font-size:4px;background-color:#EEEDF2;" width="600" height="4">
                                                <table style="mso-line-height-rule:exactly;line-height:4px;font-size:4px;height:4px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="4">

                                                    <tr>
                                                        <td style="mso-line-height-rule:exactly;line-height:8px;font-size:8px;height:8px;" height="8" bgcolor=""></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;background-color:#EEEDF2;" width="600" height="18">
                                                <table style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="18">

                                                    <tr>
                                                        <td style="mso-line-height-rule:exactly;line-height:36px;font-size:36px;height:36px;" height="36" bgcolor=""></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        <script type="application/ld+json">
                                            { "@context": "http://schema.org", "@type": "EventReservation", "reservationNumber": "973030087", "reservationStatus": "http://schema.org/Confirmed", "modifyReservationUrl": "https://www.eventbrite.com/mytickets/973030087?utm_campaign=order_confirm&amp;utm_medium=email&amp;ref=eemailordconf&amp;utm_source=eb_email&amp;utm_term=googlenow", "underName": { "@type": "Person", "name": "James Muriithi" }, "reservationFor": { "@type": "Event", "name": "ALC MEETUP 1.0 [ Mombasa ]", "startDate": "2019-06-29T08:00:00+03:00", "endDate": "2019-06-29T14:00:00+03:00", "location": { "@type": "Place", "name": "Swahili Box", "address": { "@type": "PostalAddress", "streetAddress": "Sir Mbarak Hinawy Road", "addressLocality": "Mombasa", "addressRegion": "Mombasa County", "postalCode": "", "addressCountry": "KE" } } } }
                                        </script>

                                        <!-- Footer Section-->

                                        <tr bgcolor="#EEEDF2">
                                            <td style="background-color:#EEEDF2;" bgcolor="#EEEDF2" class="grid__col">
                                                <table style="
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
        width:100%;
    " align="center">
                                                    <tr class="row_section" style="" bgcolor="#EEEDF2">
                                                        <td width="30" bgcolor="#EEEDF2" align="center"></td>
                                                        <td class="" style="text-align:center;background-color:#EEEDF2;" align="center">
                                                            <table style="
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
        width:100%;
    color:#1E0A3C;" class="" cellspacing="0" cellpadding="0" bgcolor="#EEEDF2" align="center">

                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="">

                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="">

                                                                       

                                                                    </td>
                                                                </tr>

                                                                </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="mso-line-height-rule:exactly;line-height:12px;font-size:12px;background-color:#EEEDF2;" width="600" height="12">
                                                                        <table style="mso-line-height-rule:exactly;line-height:12px;font-size:12px;height:12px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="12">

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:24px;font-size:24px;height:24px;" height="24" bgcolor=""></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>

                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="">

                                                                                <span class="social-logo-container" style="
        padding:0;
        display:inline-block;
        height:auto;
        margin:0;
        width:40px;
        text-align:center;
    ">
        <a href="#" target="_blank" aria-label="Eventbrite&#39;s Twitter">
            <img src="https://cdn.evbstatic.com/s3-build/perm_001/c07442/django/images/emails_2018_rebrand/TW-icon-purple@2x.png"
                 class="footer-social-logo__image"
                 style="
        height:24px;
        padding:0;
        width:24px;
    "
                 height="24" width="24" alt="twitter" title="twitter" border="0" />
        </a>
    </span>

                                                                        <span class="social-logo-container" style="
        padding:0;
        display:inline-block;
        height:auto;
        margin:0;
        width:40px;
        text-align:center;
    ">
        <a href="#" target="_blank" aria-label="Eventbrite Facebook">
            <img src="https://cdn.evbstatic.com/s3-build/perm_001/ac2bf4/django/images/emails_2018_rebrand/FB-icon-purple@2x.png"
                 class="footer-social-logo__image"
                 style="
        height:24px;
        padding:0;
        width:24px;
    "
                 height="24" width="24" alt="facebook" title="facebook" border="0" />
        </a>
    </span>

                                                                        <span class="social-logo-container" style="
        padding:0;
        display:inline-block;
        height:auto;
        margin:0;
        width:40px;
        text-align:center;
    ">
        <a href="#" target="_blank" aria-label="Eventbrite&#39;s Instagram">
            <img src="https://cdn.evbstatic.com/s3-build/perm_001/009b0f/django/images/emails_2018_rebrand/IG-icon-purple@2x.png"
                 class="footer-social-logo__image"
                 style="
        height:24px;
        padding:0;
        width:24px;
    "
                 height="24" width="24" alt="instagram" title="instagram" border="0" />
        </a>
    </span>

                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="mso-line-height-rule:exactly;line-height:10px;font-size:10px;background-color:#EEEDF2;" width="600" height="10">
                                                                        <table style="mso-line-height-rule:exactly;line-height:10px;font-size:10px;height:10px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="10">

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:20px;font-size:20px;height:20px;" height="20" bgcolor=""></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>

                                                                


                                                                <tr style="" bgcolor="#EEEDF2">
                                                                    <td class="footer-content" style="
        padding: 0;
     text-align:center;" align="center" bgcolor="#EEEDF2" width="100%" height="24">

                                                                                <span style="
        font-weight: normal;
        margin: 4px 0;

        font-size: 12px;
        line-height: 18px;

        font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif;
     color:#4B4D63;font-weight:normal;" class="">

            Copyright &copy; 2019 BUS. All rights reserved.

    </span>

                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;background-color:#EEEDF2;" width="600" height="18">
                                                                        <table style="mso-line-height-rule:exactly;line-height:18px;font-size:18px;height:18px;
        border-collapse:collapse;
        border-spacing:0;
        mso-table-lspace:0pt;
        mso-table-rspace:0pt;
        border:0;
        padding:0;
    ;width:100%;" cellspacing="0" cellpadding="0" height="18">

                                                                            <tr>
                                                                                <td style="mso-line-height-rule:exactly;line-height:36px;font-size:36px;height:36px;" height="36" bgcolor=""></td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>

                                                                <img src="https://www.eventbrite.com/emails/action/?recipient=muriithijames556%40gmail.com&amp;type_id=65&amp;type=open&amp;send_id=2019-06-25&amp;list_id=9" alt="" width="1" height="1" />

                                                            </table>
                                                        </td>
                                                        <td width="30" bgcolor="#EEEDF2"></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>

                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>

</html>';
        return $message;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getIdNumber()
    {
        return $this->id_number;
    }

    /**
     * @param string $id_number
     */
    public function setIdNumber($id_number): void
    {
        $this->id_number = $id_number;
    }

}