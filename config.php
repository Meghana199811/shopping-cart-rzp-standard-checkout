<?php
    $conn = mysqli_connect("localhost","root","root","razorpay");

    if(mysqli_connect_errno()){
        echo "Fail to connect:" . mysqli_connect_error();
        exit();
    }
?>
