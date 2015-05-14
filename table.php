<?PHP
    
    echo "<head><meta charset='UTF-8'></head>";
    
    function readConfig() {
        $handle = fopen("config.txt", "r");
        
        $numberOfPages = 0;
        
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if ( $$numberOfPages == 0 ) {
                    $$numberOfPages = (int)($line);
                } else {
                    $headers = explode(";", $line);
                    return array("numberOfPages" => $$numberOfPages,
                                 "headers" => $headers);
                }
            }
        } else {
            return array("numberOfPages" => 12,
                         "headers" => array("Молочные продукты", "Овощи и травы", "Фрукты", "Ягоды", "Крупа, зерно", "Грибы", "Бобы", "Соусы", "Орехи, сухофрукты", "Мясо", "Рыба, морепродукты", "Напитки"));
        } 
        fclose($handle);
    }
    
    function readPage($currentPage, $numberOfPages, $callback, $data) {
        
        if ( $currentPage == $numberOfPages ) {
            $callback(-1, NULL);
            return;
        }
        
        $publicKey = "0AhoSk2srIyPPdEJ2YmYxTXkxNTFZMzVzUXQ2QlE1aWc";
        
        $url = "https://docs.google.com/spreadsheet/pub?key=" . $publicKey . "&gid=" . $currentPage . "&output=csv";
        
        if (($handle = fopen($url, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                
                $res = json_encode($data);
                
                $err = json_last_error();
                
                if ($err != 0) {
                    echo $currentPage;
                    echo 'Error: ';
                    foreach ($data as $key => $val) {
                        echo $key . " -> " . $val . " " . strlen(json_encode($val)) . "</br>";
                    }
                    echo "<br/><br/><br/>";
                }
                
                $spreadsheet_data[]= $data;
            }
            
            $res = json_encode($spreadsheet_data);

            $err = json_last_error();
            
            $json_errors=array( -1 => 'An unknown error occured',
                               JSON_ERROR_NONE => 'No error has occurred',
                               JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
                               JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
                               JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
                               JSON_ERROR_SYNTAX => 'Syntax error',
                               JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded');
            
            if ($err != JSON_ERROR_NONE) {
                echo $currentPage;
                echo 'Error: ';
                echo isset($json_errors[$err])?$json_errors[$err]:$json_errors[-1];
            }
            
            $res = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', function ($matches) {
                                         $sym = mb_convert_encoding(pack('H*', $matches[1]),'UTF-8','UTF-16');
                                         return $sym; }, $res);
            
            $callback($currentPage, $res);
            fclose($handle);
        }
        
        readPage($currentPage+1, $numberOfPages, $callback, $data);
    }
    
    $config = readConfig();
    
    $numberOfPages = $config["numberOfPages"];
    $headers = $config["headers"];
    
    $data = array();
    
    $output = "";
    
    
    
    readPage(0, $numberOfPages, function($page, $info){
             
         if ( $page != -1 ) {
             
             global $headers;
             $header = $headers[$page];
             
             global $output;
             
             if ( strlen($info) > 0 ) {
                $output = $output . "\"" . $header . "\":" . $info;
             
             //             echo "<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><b>" . $header . "</b><br/><br/>";
             
                global $numberOfPages;
                if ( $page != $numberOfPages - 1 ) {
                    $output = $output . ",";
                }
             }
             

             
         } else {
             
             $fp = fopen('table.txt', 'w');
             
             global $output;
             $output = "{" . $output . "}";
             
             if ( strlen($output) > 500 ) {
//                 echo $output;
                 fwrite($fp, $output);
             }
             fclose($fp);
         }
    }, $data);

?>