<?php
$postData = file_get_contents("php://input");
file_put_contents("raw_post_data.log", $postData);

$data = json_decode($postData, true);

if ($data === null) {
    http_response_code(400);
    echo "Invalid JSON data received";
    exit;
}

$requiredKeys = ['movieName', 'screenNumber', 'seatNumber', 'ticketDate', 'ticketTime', 'ticketPrice', 'totalAmount', 'parkingslots', 'parkingPrices', 'totalPriceofparking', 'foodItems'];
foreach ($requiredKeys as $key) {
    if (!array_key_exists($key, $data)) {
        http_response_code(400);
        echo "Missing key '$key' in JSON data";
        exit;
    }
}

$servername = "localhost";
$username = "root";
$password = "";
$database = "nilacinemas";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt_ticket = $conn->prepare("INSERT INTO tickets_detail (movie_name, screen_number, seat_number, ticket_date, ticket_time, ticket_price, parking_slots, parking_price, total_parking_price, food_name, food_price, food_quantity, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"); 

if (!$stmt_ticket) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}

// Assign values from JSON data
$movieName = $data['movieName'];
$screenNumber = $data['screenNumber'];
$seatNumber = $data['seatNumber'];
$ticketDate = $data['ticketDate'];
$ticketTime = $data['ticketTime'];
$ticketPrice = $data['ticketPrice'];
$totalAmount = $data['totalAmount'];
$parkingslots = json_encode($data['parkingslots']); 
$parkingPrices = json_encode($data['parkingPrices']); 
$totalPriceofparking = $data['totalPriceofparking'];

$combinedFoodNames = [];
$combinedFoodPrices = [];
$combinedFoodQuantities = [];
$combinedFoodSubtotals = [];

$foodItems = $data['foodItems'];

foreach ($foodItems as $foodItem) {
    $foodName = $foodItem['foodname'];
    $foodPrice = $foodItem['price'];
    $foodQuantity = $foodItem['quantity'];
    $foodSubtotal = $foodItem['subtotal'];
    
    $index = array_search($foodName, $combinedFoodNames);
    if ($index !== false) {
        $combinedFoodQuantities[$index] += $foodQuantity;
        $combinedFoodSubtotals[$index] += $foodSubtotal;
    } else {
        $combinedFoodNames[] = $foodName;
        $combinedFoodPrices[] = $foodPrice;
        $combinedFoodQuantities[] = $foodQuantity;
        $combinedFoodSubtotals[] = $foodSubtotal;
    }
}

$combinedFoodNameString = implode(",", $combinedFoodNames);

$combinedFoodPriceString = implode(",", $combinedFoodPrices);

$combinedFoodQuantityString = implode(",", $combinedFoodQuantities);

$stmt_ticket->bind_param("sssssssssssss", $movieName, $screenNumber, $seatNumber, $ticketDate, $ticketTime, $ticketPrice, $parkingslots, $parkingPrices, $totalPriceofparking, $combinedFoodNameString, $combinedFoodPriceString, $combinedFoodQuantityString, $totalAmount);

$stmt_ticket->execute();

if ($stmt_ticket->errno) {
    die("Execute failed: (" . $stmt_ticket->errno . ") " . $stmt_ticket->error);
}

$stmt_ticket->close();

$conn->close();

echo "Data received and stored successfully";
?>
