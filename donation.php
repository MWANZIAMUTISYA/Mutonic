
<?php
// Process payment with M-Pesa API

// Get JSON data from request
$data = json_decode(file_get_contents("php://input"), true);
$phone = $data['phone'];
$amount = $data['amount'];

// M-Pesa API credentials (These should be kept secure)
$shortcode = '174379';
$lipa_na_mpesa_shortcode = '174379';
$lipana_online_shortcode = '174379';
$lipana_online_shortcode_passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
$lipana_online_shortcode_shortcode = '174379';

// Safaricom credentials for generating the access token
$consumer_key = "ASoDQiSkncrvZlhQBpMDsSBf4iviYFFVoq40pKzxMLM4Vbyd"; // Change to your actual key
$consumer_secret = "XRfUeQv3lbIO7hfPlYDRMQvwhGt5c4rkXCXhzCEpRUiT2OdIL32SAtkOfDAKd8CN"; // Change to your actual secret

// Step 1: Generate access token
function getAccessToken($consumer_key, $consumer_secret) {
    $url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    $headers = [
        "Authorization: Basic " . base64_encode("$consumer_key:$consumer_secret")
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response);
    return $data->access_token;
}

$access_token = getAccessToken($consumer_key, $consumer_secret);

// Step 2: Initiate payment request (Lipa na M-Pesa)
$payment_url = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
$headers = [
    "Authorization: Bearer $access_token",
    "Content-Type: application/json"
];

$payload = [
    "BusinessShortcode" => $shortcode,
    "LipaNaMpesaOnlineShortcode" => $lipa_na_mpesa_shortcode,
    "PhoneNumber" => $phone,
    "Amount" => $amount
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $payment_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
curl_close($ch);

echo json_encode(['message' => 'Payment successful, please check your M-Pesa']);
?>

</html>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Donation & Purchase</title>
</head>
<body>
    <style>
        body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f5f5f5;
}

header {
    background-color: #3a8d99;
    color: white;
    padding: 20px;
    text-align: center;
}

#book-section {
    padding: 20px;
}

#book-list {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

#action-section, #payment-section {
    padding: 20px;
    margin-top: 20px;
    background-color: #fff;
    border-radius: 5px;
    box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
}

    </style>

    <header>
        <h1>Buy and Donate Books</h1>
    </header>

    <section id="book-section">
        <h2>Available Books</h2>
        <!-- Dynamically load books here -->
        <div id="book-list"></div>
    </section>

    <section id="action-section">
        <h2>Choose an Option</h2>
        <button id="buyButton">Buy Book</button>
        <button id="donateButton">Donate Book</button>
    </section>

    <section id="payment-section" style="display:none;">
        <h3>Payment Details</h3>
        <form id="payment-form">
            <label for="phone">M-Pesa Phone Number:</label>
            <input type="text" id="phone" name="phone" required>
            <label for="amount">Amount (KES):</label>
            <input type="number" id="amount" name="amount" required>
            <button type="submit">Pay via M-Pesa</button>
        </form>
    </section>

    <script>
        // Sample data (Books)
const books = [
    { id: 1, title: "Book 1", price: 500 },
    { id: 2, title: "Book 2", price: 300 },
    { id: 3, title: "Book 3", price: 450 }
];

// Render Books
const bookList = document.getElementById('book-list');
books.forEach(book => {
    const bookItem = document.createElement('div');
    bookItem.innerHTML = `<div class="book-card">
                            <h3>${book.title}</h3>
                            <p>Price: KES ${book.price}</p>
                          </div>`;
    bookList.appendChild(bookItem);
});

// Handle Buy and Donate actions
document.getElementById('buyButton').addEventListener('click', () => {
    showPaymentSection();
    document.getElementById('amount').value = 500; // Assuming the user buys a book for 500
});

document.getElementById('donateButton').addEventListener('click', () => {
    showPaymentSection();
    document.getElementById('amount').value = 300; // Donation amount
});

function showPaymentSection() {
    document.getElementById('payment-section').style.display = 'block';
}

// Handle payment form submission
document.getElementById('payment-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const phone = document.getElementById('phone').value;
    const amount = document.getElementById('amount').value;
    
    // Send data to PHP backend for M-Pesa payment
    fetch('process_payment.php', {
        method: 'POST',
        body: JSON.stringify({ phone: phone, amount: amount }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(data => alert(data.message))
    .catch(error => console.error('Error:', error));
});


    </script>

</body>
?>