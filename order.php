<?php
$conn = pg_connect("host=localhost dbname=dairich_db user=postgres password=Ojasvi13");

if (!$conn) {
    die("Connection failed ❌");
}

$distributor_id = $_POST['distributor_id'];
$variant_id = $_POST['variant_id'];
$quantity = $_POST['quantity'];

$price_query = "SELECT price FROM IceCream_Variants WHERE variant_id = $variant_id";
$price_result = pg_query($conn, $price_query);

$row = pg_fetch_assoc($price_result);
$price = $row['price'];

$total_amount = $price * $quantity;

$order_query = "INSERT INTO Orders (distributor_id, order_date, total_amount, status)
VALUES ($distributor_id, CURRENT_DATE, $total_amount, 'Pending') RETURNING order_id";

$order_result = pg_query($conn, $order_query);
$order_row = pg_fetch_assoc($order_result);
$order_id = $order_row['order_id'];

$item_query = "INSERT INTO Order_Items (order_id, variant_id, quantity_boxes)
VALUES ($order_id, $variant_id, $quantity)";

pg_query($conn, $item_query);

echo "Order placed successfully ✅ Total: ₹" . $total_amount;
?>