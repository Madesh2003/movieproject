<?php
$postData = file_get_contents("php://input");
file_put_contents("raw_post_data.log", $postData);

$data = json_decode($postData, true);

if ($data === null) {
    http_response_code(400);
    echo "Invalid JSON data received";
    exit;
}

$requiredKeys = ['foodItems'];
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

$stmt_food = $conn->prepare("INSERT INTO food_details (foodname, price, quantity, subtotal) VALUES (?, ?, ?, ?)"); 

if (!$stmt_food) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}

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

$combinedFoodSubtotal = implode(",", $combinedFoodSubtotals);
$stmt_food->bind_param("ssss", $combinedFoodNameString, $combinedFoodPriceString, $combinedFoodQuantityString, $combinedFoodSubtotal);

$stmt_food->execute();

if ($stmt_food->errno) {
    die("Execute failed: (" . $stmt_food->errno . ") " . $stmt_food->error);
}

$stmt_food->close();

$conn->close();

echo "Food data received and stored successfully";
?>
