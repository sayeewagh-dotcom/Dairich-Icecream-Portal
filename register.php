<?php
$conn = pg_connect("host=localhost dbname=dairich_db user=postgres password=Ojasvi13");

$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];
$phone = $_POST['phone'];
$address = $_POST['address'];

$query = "INSERT INTO Distributors (dname, email, dpassword, phone, address) 
VALUES ('$name', '$email', '$password', '$phone', '$address')";

$result = pg_query($conn, $query);

if ($result) {
    echo "Registered successfully ";
} else {
    echo "Error ";
}
?>