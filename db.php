<?php
$conn = pg_connect("host=localhost dbname=dairich_db user=postgres password=Ojasvi13");

if (!$conn) {
    echo "Connection failed ";
} else {
    echo "Connected successfully ";
}
?>