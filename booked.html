<!DOCTYPE html>
<html>

<head>
    <!-- for-mobile-apps -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="theme-color" content="#25274d">
    <meta name="description" content="A bus seat reservation system made by James">
    <meta name="author" content="James Muriitih">
    <meta name="keywords" content="bus,bus seats,">
    <meta property="og:site_name" content="google.com">
    <meta property="og:title" content="Bus Seat Reservation System" />
    <meta property="og:description" content="A bus seat reservation system made by James" />
    <meta property="og:image:url" itemprop="image" content="A bus seat reservation system made by James">
    <meta property="og:image" content="https://james-muriithi.github.io/bus/images/logo-400.png" />
    <meta property="og:image:url" content="https://james-muriithi.github.io/bus/images/logo-400.png" />
    <meta property="og:image:secure_url" content="https://james-muriithi.github.io/bus/images/logo-400.png" />
    <meta property="og:image:type" content="image/png" />
    <meta property="og:image:width" content="400" />
    <meta property="og:image:height" content="400" />
    <meta property="og:locale" content="en_GB" />
    <meta property="og:type" content="website" />
    <!-- //for-mobile-apps -->
    <title>Bus Seat Reservation System</title>
    <!-- icons -->
    <link rel="icon" type="image/png" href="images/logo-96.png" sizes="96x96" />
    <link rel="icon" type="image/png" href="images/logo-16.png" sizes="16x16" />
    <link rel="icon" type="image/png" href="images/logo-32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="images/logo-64.png" sizes="64x64" />
    <link rel="icon" type="image/png" href="images/logo-128.png" sizes="128x128" />
    <!-- end of icons  -->
    <!-- css -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/nice-select.css">
    <link rel="stylesheet" type="text/css" href="css/util.css">
    <link rel="stylesheet" type="text/css" href="fonts/font-awesome/css/font-awesome.min.css">
    <link href="fonts/material-design-icons/material-icon.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="plugins/DataTables/datatables.min.css"/>
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <!-- end of css -->
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-dark static-top">
    <div class="container">
        <a class="navbar-brand" href="index.html">
            <h1>
                <img src="images/p1.png" alt="logo" width="30" height="30" style="margin-top: -10px">
                <span>B</span>
                <span>U</span>
                <span>S</span>
            </h1>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ml-auto">
                <!-- <li class="nav-item active">
                    <a class="nav-link" href="#">Reprint
                        <span class="sr-only">(current)</span>
                    </a>
                </li> -->
                <li class="nav-item">
                    <button class="btn btn-outline-warning my-2 my-sm-0 ml-3" type="submit">Login</button>
                </li>
                <li class="nav-item">
                    <button class="btn btn-outline-warning my-2 my-sm-0 ml-2" type="submit">Sign Up</button>
                </li>
                <!-- <li class="nav-item">
          <a class="nav-link" href="#">Contact</a>
        </li> -->
            </ul>
        </div>
    </div>
</nav>
<!-- </navbar -->
<!-- main content -->
<main class="col-md-12">
    <div class="col-md-11 col-lg-9 col-xl-10 mx-auto window">
        <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered" style="width:100%">
                <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Id #</th>
                    <th>Phone</th>
                    <th>Pickup</th>
                    <th>Seat No</th>
                    <th>Date</th>
                    <th>Paid</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</main>
</body>

</html>
<!-- javascript -->
<script type="text/javascript " src="js/jquery.min.js "></script>
<script type="text/javascript " src="js/popper.min.js "></script>
<script type="text/javascript " src="js/bootstrap.min.js "></script>
<script type="text/javascript " src="js/bootstrapValidator.js"></script>
<script type="text/javascript " src="js/jquery-easing.min.js "></script>
<script type="text/javascript " src="js/jquery.nice-select.js "></script>

<!--datatables-->
<script type="text/javascript" src="plugins/DataTables/datatables.min.js"></script>
<!--end-->

<script type="text/javascript">
    $(document).ready( function () {
        //https://examinationcomplaint.theschemaqhigh.co.ke/HCI/api/book/
        $data = [];

        $('#example').DataTable({
            dom: 'Bfrtip',
            order: [6,'desc'],
            buttons: [
                {
                    extend: 'pdf',
                    exportOptions: {
                        columns: 'th:not(:last-child)'
                    }
                },
                {
                    extend: 'print',
                    exportOptions: {
                        columns: 'th:not(:last-child)'
                    }
                }
            ],
            'ajax' : {
                url : 'https://examinationcomplaint.theschemaqhigh.co.ke/HCI/api/book/?bus_id=1&show_booked_seats',
                "dataType": "json",
                "type": "GET"
            },
            'columns' : [
                { 'data' : "fullname" },
                { 'data' : "email" },
                { 'data' : "id_number" },
                { 'data' : "phone" },
                {
                    data : null,
                    render: function ( data, type, row ) {
                        return data.route.split('-')[0]
                    }
                },
                { 'data' : "seat_no" },
                { 'data' : "booking_date" },
                { data : null,
                    render: function ( data, type, row ) {
                        return Number(data.paid) == 0 ? 'No': 'Yes'
                    }
                },
                {
                    data: null,
                    render: function ( data, type, row ) {
                        if (Number(data.paid) == 0) return '<button class="btn btn-sm btn-success btn-set-pay fs-11" id='+data.id+' data-seat='+data.seat_no+'>' +
                            'fully paid' +
                            '</button>';
                        return '';
                    }
                }
            ]
        })
        $('body').on('click','button.btn-set-pay',function (event) {
            let id = $(event.target).attr('id');
            $.ajax({
                method: 'GET', //https://examinationcomplaint.theschemaqhigh.co.ke/HCI/api/book/
                url: 'https://examinationcomplaint.theschemaqhigh.co.ke/HCI/api/book/?set_paid&id='+id,
                success: function (data) {
                    $data = JSON.stringify(data);
                    console.log(data)
                    location.reload()
                    if (data.success){
                        // window.location.href = 'https://examinationcomplaint.theschemaqhigh.co.ke/HCI/api/print-ticket.php?bid=1&seat_no='
                        //     +$(event.target).data('seat');
                    }
                },
                error: function (data) {
                    console.log(data)
                }
            });
        })
    } );
</script>
<!-- end of js -->