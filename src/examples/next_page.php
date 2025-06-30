<?php
session_start();
echo "<html>";

$queryString = $_SERVER["QUERY_STRING"];
echo "Query string of the incoming URL: " . $queryString . "<br>";

echo "Cookies received:<br>";
foreach ($_COOKIE as $name => $value) {
    echo "$name = $value<br>";
}

echo "<br>";

$myLogin = $_SESSION["myLogin"];
echo "Value of myLogin has been retrieved: " . $myLogin . "<br>";

$myColor = $_SESSION["myColor"];
echo "Value of myColor has been retrieved: " . $myColor . "<br>";

echo "</html>";
?>