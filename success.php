<?php
require_once 'config.php';
echo '<pre> '; print_r($_POST);
$sql = "INSERT INTO payment_laser (payment_id, order_id, signature_hash)
        VALUES ('".$_POST['razorpay_payment_id']."', '".$_POST['razorpay_order_id']."', '".$_POST['razorpay_signature']."' )";

    if($conn->query($sql)==TRUE){
        echo "New Record created successfully";
    }else
    {
        echo "error:". $sql . "<br>" . $conn->error;
    }
    $conn->close();
    die;
?>