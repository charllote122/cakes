<?php
session_start();

// M-Pesa credentials
$consumerKey = 'OpAEKPvyFH0oEOPIE3NBAjaj8gikND2EIWiLoXITfSFqImJf'; 
$consumerSecret = '85pjjTxDQs8KScHFuwGaxLm0EbYGjyulosUBbjJCvP1sZG91XaE2IZj8txRRky9h'; 
$shortcode = '174379'; 
$passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'; 
$callbackUrl = 'https://yourdomain.com/callback.php'; // Replace with your actual callback URL

// Initialize total price
$totalPrice = 0;

if (!empty($_SESSION['cart'])) {
    // Calculate total price if the cart is not empty
    foreach ($_SESSION['cart'] as $item) {
        $quantity = isset($item['quantity']) ? $item['quantity'] : 1;
        $totalPrice += $item['price'] * $quantity;
    }
} else {
    die('Your cart is empty. Please add products to the cart.');
}

// Function to get M-Pesa access token
function getAccessToken($consumerKey, $consumerSecret) {
    $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        echo 'Error:' . curl_error($curl);
        return null;
    }
    curl_close($curl);

    $json = json_decode($response, true);
    return isset($json['access_token']) ? $json['access_token'] : null;
}

// Function to initiate M-Pesa STK Push
function lipaNaMpesaOnline($phoneNumber, $totalPrice, $accessToken, $shortcode, $passkey, $callbackUrl) {
    $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    $timestamp = date('YmdHis');
    $password = base64_encode($shortcode . $passkey . $timestamp);

    $payload = array(
        'BusinessShortCode' => $shortcode,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $totalPrice, 
        'PartyA' => $phoneNumber,
        'PartyB' => $shortcode, 
        'PhoneNumber' => $phoneNumber, 
        'CallBackURL' => $callbackUrl, 
        'AccountReference' => 'CakeShop', 
        'TransactionDesc' => 'Payment for cakes'
    );

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accessToken, 'Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($curl);
    curl_close($curl);

    return json_decode($response, true);
}

// Handle form submission for payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phoneNumber = $_POST['phone_number'];
    $phoneNumber = preg_replace('/^0/', '254', $phoneNumber); // Convert 07XXXXXXXX to 254XXXXXXXXX format

    if (empty($phoneNumber)) {
        die('Phone number is required.');
    }

    // Get M-Pesa access token
    $accessToken = getAccessToken($consumerKey, $consumerSecret);
    if (!$accessToken) {
        die('Failed to get M-Pesa access token.');
    }

    // Initiate M-Pesa payment
    $response = lipaNaMpesaOnline($phoneNumber, $totalPrice, $accessToken, $shortcode, $passkey, $callbackUrl);

    // Check response from M-Pesa
    if (isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
        echo 'Payment initiated successfully. Please check your phone for the payment prompt.';
    } else {
        echo 'Failed to initiate payment: ' . (isset($response['errorMessage']) ? $response['errorMessage'] : 'Unknown error');
    }

    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e9ecef;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        h2 {
            color: #333;
            text-align: center;
        }
        form {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"] {
            width: calc(100% - 20px);
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus {
            border-color: #80bdff;
            outline: none;
        }
        button {
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
            width: 100%;
        }
        button:hover {
            background-color: #0056b3;
        }
        @media (max-width: 400px) {
            form {
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <div>
        <h2>Payment for Cakes</h2>
        <p>Total Amount to be Paid: <strong>Ksh <?php echo number_format($totalPrice, 2); ?></strong></p>
       
        <form method="POST" action="">
            <label for="phone_number">Enter your phone number:</label>
            <input type="text" name="phone_number" id="phone_number" required placeholder="07XXXXXXXX">
            <button type="submit">Pay Now</button>
        </form>
         <p><a href="../index.php">Return to Homepage</a></p>
    </div>
</body>
</html>
