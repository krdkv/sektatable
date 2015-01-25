<?PHP
    
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
                $spreadsheet_data[]=$data;
            }

            $res = json_encode($spreadsheet_data);
            
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
    
    readPage(0, 12, function($page, $info){
             
         if ( $page != -1 ) {
             
             global $headers;
             $header = $headers[$page];
             
             global $output;
             $output = $output . "\"" . $header . "\":" . $info;
             global $numberOfPages;
             if ( $page != $numberOfPages - 1 ) {
                $output = $output . ",";
             }
             
         } else {
             
             $fp = fopen('table.txt', 'w');
             
             global $output;
             $output = "{" . $output . "}";
             
             if ( strlen($output) > 500 ) {
                 echo $output;
                 fwrite($fp, $output);
             }
             fclose($fp);
         }
    }, $data);

?>