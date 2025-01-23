<?
 $hebrew_words = array(
        0 => "",
        1=> "א",
       2 => "ב",
       3 => "ג",
       4 => "ד",
       5 => "ה",
       6 => "ו",
       7 => "ז",
       8 => "ח",
       9 => "ט",
       10 => "י",
       20 => "כ",
       30 => "ל",
       40 => "מ",
       
       50 => "נ",
       60 => "ס",
       70 => "ע",
        80 => "פ",
        90 => "צ",
        100 => "ק",
        200 => "ר",
        300 => "ש",
        400 => "ת"
    );
define("HEBREW_LETTERS",$hebrew_words);
 
  function mb_str_split( $string ) {
 # Split at all position not after the start: ^
 # and not before the end: $
 return preg_split('/(?<!^)(?!$)/u', $string );
 }
               
function gematriya($word){
    //error_log("gematriya: ".strval($word));
    $letters = mb_str_split($word);
    $num = 0;
    foreach($letters as $letter){
        $key = array_search ($letter, HEBREW_LETTERS);
        if($key){
            $num += $key;
        }
    }
    return $num;
} 
function gematriyaNumToLett($number){
   
    $n400=0;
    $number=intval($number);
    $z=400;
    while($number>$z){
        $n400++;
        $number=$number - $z;
    }
    $n100=intval($number/100)*100;
    $n10=intval($number%100/10)*10;
    $n1=$number%100%10;
    if($n10 ==10 && ($n1==5 ||$n1==6)){
        $n10 = 9;
        $n1 = $n1 + 1;
    }
    //error_log("number $number $n400 $n100 $n10 $n1");
    $letter="";
    for($i=0 ; $i<$n400 ; $i++,$letter.=HEBREW_LETTERS[400] );
    $letter .= HEBREW_LETTERS[$n100].HEBREW_LETTERS[$n10].HEBREW_LETTERS[$n1];
    //str_replace("")
    return $letter;
} 

add_action('wp_ajax_import_masechtos', 'import_masechtos');
add_action('wp_ajax_nopriv_import_masechtos', 'import_masechtos');
function import_masechtos(){
    error_log('import_masechtos');
    
    try
    {
        $fileName = realpath(dirname(__FILE__))."/assets/mashecetcount.csv";
        $file = fopen($fileName, "r");
        $mashechet_arr = array();
        while (($getData = fgetcsv($file, 10000, ",")) !== FALSE)
        {
            if(isset($getData[0]) && isset($getData[1]) && isset($getData[2])){
                $mashechet_arr[$getData[1]]=array("count"=>$getData[2],"seder"=>$getData[0]);
               
            }
        }
        error_log("mashechet_arr: ".json_encode($mashechet_arr));
        fclose($file);
        
        $fileName = realpath(dirname(__FILE__))."/assets/preho.csv";
        //$fileName = realpath(dirname(__FILE__))."/assets/brachot2.csv";
          if ( !file_exists($fileName) ) {
            error_log('File not found.'.$fileName);
            echo json_encode("File not found");
            die();
        }
    
        $file = fopen($fileName, "r");
        if ( !$file ) {
            error_log('File open failed.');
            echo json_encode("File open failed");
            die();
        }
        
        
       // $last_daf = false;  
        while (($getData = fgetcsv($file, 10000, ",")) !== FALSE)
        {
            //error_log("line ".json_encode($getData));
            if(isset($getData[0]) && isset($getData[1]) && isset($getData[2]) && isset($getData[3])){
                $perek_name = $next_perek_name;
                $masechet = $next_masechet;
                $perek_number = $next_perek_number;
                $first_daf = $last_daf;
                $vowels = array(".", ":");
                $first = gematriya(str_replace($vowels,"",$first_daf));
                $last_daf = $getData[3];
                
                error_log("first_daf: ".$first_daf." last_daf: ".$last_daf);
                if($first>0){
                    $last = gematriya(str_replace($vowels,"",$last_daf));
                    if($last==2){
                        $last = $mashechet_arr[$masechet]["count"]+2;
                         //$last=  $count_daf - $first;
                    }
                    error_log(  "first: ".$first." last: ".$last);
                    for($i=$first;$i<$last;$i++){
                        $daf = gematriyaNumToLett($i);
                        $query = "insert into wp_y1_shaspages (seder, masechet,chapter,chapter_name, page) 
                        VALUES ('".$mashechet_arr[$masechet]["seder"]."', '$masechet', '$perek_number' ,'$perek_name', '$daf\.')";
                        // error_log($query);
                         run_query($query);
                        $query = "insert into wp_y1_shaspages (seder, masechet,chapter,chapter_name, page)
                        VALUES ('".$mashechet_arr[$masechet]["seder"]."', '$masechet', '$perek_number' ,'$perek_name', '$daf\:')";
                       // error_log($query);
                        run_query($query);
                    }
                }
               
                $next_perek_name = $getData[0];
                $next_masechet = $getData[1];
                $next_perek_number = $getData[2];
               // $next_first_daf = $getData[3];
               error_log("perek_number:".$perek_number);
    
            }
        }
        
    
        fclose($file);
        echo json_encode("finish");
        die();

      
    
      // send success JSON
    
    } catch ( Exception $e ) {
         echo json_encode($e);
        die();
      // send error message if you can
    } 
}

?>