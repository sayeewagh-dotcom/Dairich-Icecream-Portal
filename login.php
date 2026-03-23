<?php
$conn = pg_connect("host=localhost dbname=dairich_db user=postgres password=Ojasvi13");

if (!$conn) {
    die("Connection failed ❌");
}

$email = $_POST['email'];
$password = $_POST['password'];

$query = "SELECT * FROM Distributors 
WHERE email='$email' AND dpassword='$password'";

$result = pg_query($conn, $query);

if (pg_num_rows($result) > 0) {
    echo "Login successful ✅";
} else {
    echo "Invalid credentials ❌";
}
?>