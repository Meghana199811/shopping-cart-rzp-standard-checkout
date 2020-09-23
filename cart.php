<?php
session_start();
require_once ("php/CreateDb.php");
require_once ("php/component.php");

$db = new CreateDb("Productdb", "Producttb");

    if (isset($_POST['remove'])){
        if ($_GET['action'] == 'remove'){
            foreach ($_SESSION['cart'] as $key => $value){
                if($value["product_id"] == $_GET['id']){
                    unset($_SESSION['cart'][$key]);
                    echo "<script>alert('Product has been Removed...!')</script>";
                    echo "<script>window.location = 'cart.php'</script>";
                }
            }
        }
    }
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Cart</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.8.2/css/all.css" />

    <!-- Bootstrap CDN -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<?php
    require_once ('php/header.php');
?>

<div class="container-fluid">
    <div class="row px-5">
        <div class="col-md-7">
            <div class="shopping-cart">
                <h6>My Cart</h6>
                <hr>

                <?php

                $total = 0;
                $finaltotal = 0;
                    if (isset($_SESSION['cart'])){
                        $product_id = array_column($_SESSION['cart'], 'product_id');
                        $result = $db->getData();
                        while ($row = mysqli_fetch_assoc($result)){
                            foreach ($product_id as $id){
                                if ($row['id'] == $id){
                                    cartElement($row['product_image'], $row['product_name'],$row['product_price'], $row['id']);
                                    $total = $total + (int)$row['product_price'];
                                    $finaltotal = str_pad("$total", 6, '00', STR_PAD_RIGHT);
                                }
                            }
                        }
                    }else{
                        echo "<h5>Cart is Empty</h5>";
                    }

                ?>

            </div>
        </div>
        <div class="col-md-4 offset-md-1 border rounded mt-5 bg-white h-25">
            <div class="pt-4">
                <h6>PRICE DETAILS</h6>
                <hr>
                <div class="row price-details">
                    <div class="col-md-6">
                        <?php
                            if (isset($_SESSION['cart'])){
                                $count  = count($_SESSION['cart']);
                                echo "<h6>Price ($count items)</h6>";
                            }else{
                                echo "<h6>Price (0 items)</h6>";
                            }
                        ?>
                        <h6>Delivery Charges</h6>
                        <hr>
                        <h6>Amount Payable</h6>
                    </div>
                    <div class="col-md-6">
                        <h6>&#8377;<?php echo $total; ?></h6>
                        <h6 class="text-success">FREE</h6>
                        <hr>
                        <h6>&#8377;<?php
                            echo $total;
                            ?>
                        </h6>
                    </div>
                </div>
            </div>  


<!-- Razorpay standard checkout integration -->
<?php
require_once 'config.php';
require_once 'razorpay-php/Razorpay.php'; 

use Razorpay\Api\Api;

$keyid = 'rzp_test_RJogBiFApOZ20E';
$secretkey = 'HrMjVKU708jjrH3eJGDeb8Ek';
$api = new Api($keyid, $secretkey);

// create order

$order = $api->order->create(array(
    'receipt' => '123',
    'amount' => $finaltotal,
    'payment_capture' => 1,
    'currency' => 'INR',
    )
  );
?>

<!-- automatic checkout -->

<link rel="stylesheet" href="cart.css" type="text/css">
<form action="verify.php" method="post">
    <script
        src="https://checkout.razorpay.com/v1/checkout.js"
        data-key="<?php echo $keyid ?>" // Enter the Key ID generated from the Dashboard
        data-amount="<?php echo $order->Amount ?>" // Amount is in currency subunits. Default currency is INR. Hence, 50000 refers to 50000 paise
        data-currency="INR"
        data-order_id="<?php echo $order->id ?>"//This is a sample Order ID. Pass the `id` obtained in the response of the previous step.
        data-buttontext="Pay Now"
        data-name="Meghana"
        data-description="Shopping-Cart"
        data-image="logo.jpg"
        data-prefill.name="Meghana"
        data-prefill.email="meghanar113@gmail.com"
        data-prefill.contact="8147986823"
        data-theme.color="#4285f4">
    </script>
    <input type="hidden" custom="Hidden Element" name="hidden" >
</form>



<!-- manual checkout with handler function -->

<!-- <link rel="stylesheet" href="cart.css" type="text/css">
<button id="rzp-button1" class="razorpay-payment-button">Pay Now</button>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<form name='razorpayform' action="http://localhost:8888/standard/verify.php" method="POST">
    <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
    <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
    <input type="hidden" name="razorpay_signature"  id="razorpay_signature" >
</form>
<script>
var options = {
    "key": "<?php echo $keyid ?>", // Enter the Key ID generated from the Dashboard
    "amount": "<?php echo $order->Amount ?>", // Amount is in currency subunits. Default currency is INR. Hence, 50000 refers to 50000 paise
    "currency": "INR",
    "name": "Meghana",
    "description": "Shopping-cart",
    "image": "logo.jpg",
    "order_id": "<?php echo $order->id ?>", //This is a sample Order ID. Pass the `id` obtained in the response of Step 1
    "handler": function (response){
        document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
        document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
        document.getElementById('razorpay_signature').value = response.razorpay_signature;
        document.razorpayform.submit();
    },
    "prefill": {
        "name": "Meghana",
        "email": "meghanar113@example.com",
        "contact": "8147986823"
    },
    "notes": {
        "address": "Online Shopping-cart"
    },
    "theme": {
        "color": "#F37254"
    }
};
var rzp1 = new Razorpay(options);
document.getElementById('rzp-button1').onclick = function(e){
    rzp1.open();
    e.preventDefault();
}
</script> -->



<!-- manual checkout with callback URL -->

<!-- <link rel="stylesheet" href="cart.css" type="text/css">
<button id="rzp-button1" class="razorpay-payment-button">Pay Now</button>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<form action="verify.php" method="post">
<script>
var options = {
    "key": "<?php echo $keyid ?>",
    "amount": "<?php echo $order->Amount ?>",
    "currency": "INR",
    "name": "Meghana",
    "description": "Shopping-cart",
    "image": "logo.jpg",
    "order_id": "<?php echo $order->id ?>", //This is a sample Order ID. Pass the `id` obtained in the response of Step 1
    "callback_url": "success.php",
    "prefill": {
        "name": "Meghana",
        "email": "meghanar113@example.com",
        "contact": "8147986823"
    },
    "notes": {
        "details": "Online shopping"
    },
    "theme": {
        "color": "#4285f4"
    }
};
var rzp1 = new Razorpay(options);
document.getElementById('rzp-button1').onclick = function(e){
    rzp1.open();
    e.preventDefault();
}
</script> 
</form>  -->

        </div>
    </div>
</div>




<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>
