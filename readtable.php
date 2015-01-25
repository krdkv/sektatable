<?PHP
    
$p = $_GET["p"];

if ( strcmp ("fedf9c1d1c0662bae36b2cbb59898a15", md5($p)) == 0 ) {
    echo file_get_contents('./table.txt', true);
}

?>