<?php
/**
 * Theme functions and definitions
 *
 * @package Milestones
 */
require_once dirname(__FILE__)."/global-functions.php";
require_once dirname(__FILE__)."/helpers.php";
  require_once dirname(__FILE__)."/insertshaspages.php";
 
 function milestones_enqueue_scripts() {
	 wp_enqueue_style(
		'milestones',
		get_template_directory_uri() . '/style.css',[],'1.0.0'
	);
	wp_enqueue_style('menu-style',	get_template_directory_uri() . '/menu.css',[],'1.0.0'	);
	wp_enqueue_style('page-style',	get_template_directory_uri() . '/page.css',[],'1.0.0'	);
	wp_enqueue_style('design-style',	get_template_directory_uri() . '/design.css',[],'1.0.0'	);
	wp_enqueue_style('fa-style',	get_template_directory_uri() . '/assets/css/fa-all.min.css',[],'1.0.0'	);
	
 
	wp_enqueue_script('jquery');
    //wp_enqueue_script( 'script-js', get_template_directory_uri() . '/agron-plus/script.js');
  	wp_register_script( 'kendo-all', get_template_directory_uri(). '/assets/kendo.all.min.js' );	
    wp_enqueue_script( 'kendo-all' );
    
    wp_register_script( 'js-functions', get_stylesheet_directory_uri(). '/js-functions.js' );	
    wp_enqueue_script( 'js-functions' );
   
}
add_action( 'wp_enqueue_scripts', 'milestones_enqueue_scripts' );
add_action( 'after_setup_theme', 'milestones_setup_theme' );
function milestones_setup_theme(){
  register_nav_menu("side-menu","תפריט");
}
function custom_menu(){
    $header_nav_menu = wp_nav_menu( [
    	'theme_location' => 'side-menu',
    	'fallback_cb' => false,
    	'echo' => false,
    ] );
    /* <ul class="menu-list ">
		        <li class="students current-page flex-display start"><a href='https://beta.library.org.il/students'>תלמידים </a></li>
		        <li class="staff flex-display start"><a href='https://beta.library.org.il/staff'>אנשי צוות</a></li>
		        <li class="groups flex-display start"><a href='https://beta.library.org.il/groups'>קבוצות הלימוד</a></li>
		        <li class="material flex-display start"><a href='https://beta.library.org.il/material'>מגמות - חומר לימודי</a></li>
		        <li class="student flex-display start"><a href='https://beta.library.org.il/student'>תלמיד</a></l
		    </ul>*/
?>
    <div id="sidebar-menu" class="sidebar">
	    <nav class="milestones-menu" role="navigation"><?php echo $header_nav_menu;?></nav>
    </div>
<?}

add_action("wp_head","wp_head_action");
function wp_head_action (){
    if(!is_user_logged_in()){
        if(!str_contains(get_permalink(), "login")){
        ?>
            <script type="text/javascript">
                window.location.href = window.location.host + "/login";
            </script>
        <?}
    }
    else{
        if(str_contains(get_permalink(), "login")){
             echo("<script>location.href = '".home_url()."'</script>");
             exit; 
        }
        slider_message();
        popup_window();
        custom_menu();
    }
}

add_action('wp_ajax_user_logout', 'user_logout');
function user_logout()
{ 	
    wp_logout();
    die();
}

add_action('wp_ajax_run_sql', 'run_sql');
add_action('wp_ajax_nopriv_run_sql', 'run_sql');
function run_sql()
{ 

    $options = $_POST["options"];
    //error_log('run_sql '.json_encode ($options));
    //$query =  $options[0]["query"];
    $table_name = $options[0]["update_table"];
    if(isset($options[0]["query"])){
        $query = $options[0]["query"];
        $query = str_replace("\\", "", $query);
        //error_log ('insert qouery '.$query);
        $results = run_query($query);
        $id_column = get_id_column_in_page($table_name);
//        if(isset($_POST["update_table"])){
            if(isset($_POST["sub_table_value"])){
                $results =get_sub_table($_POST["update_table"],$_POST["sub_table_value"]);
                error_log ('get_sub_table '.  $results);
            }
            else /*if (!str_starts_with ($query, "delete"))*/{
                if (str_starts_with ($query, "insert")) {
                    $table_name = substr ($query, strpos ($query, "wp_y1_"),
                        strpos ($query, " (") - strpos ($query, "wp_y1_"));
                    $query = "select max(" . $id_column . ") as new_id from " . $table_name;
                    $new_row_id = run_query ($query)[0];
                }
                error_log ("max id " . json_encode ($new_row_id));
                $query = get_page_query ($table_name, $options[0]["values"][0] ?: $new_row_id->new_id);
                //error_log($query);
                $results_query = run_query ($query);
                //error_log('results_query '.json_encode ( $results_query));
                $results = get_tr_data ($table_name, $results_query, $id_column);
                //$results =str_replace("</tr>","", str_replace ("<tr>","", $results));
                //error_log('results '.json_encode ( $results));
            }
//        }
        echo json_encode (array("html_tr"=> $results));
        die();
    }
}
function run_query($query)
{ 
    global $wpdb;    
    return $wpdb->get_results( $query);
}

add_action('wp_ajax_send_site_forms', 'send_site_forms');
add_action('wp_ajax_nopriv_send_site_forms', 'send_site_forms');
function send_site_forms()
{
	//$token = $_POST['recaptcha_token'];
//	$secret = "6Ld4swoiAAAAALX62fVnlJMy5WW31nTTzwghSZyC";

	//if (check_recaptcha($token, $secret)) {
	    //try{
    	//	unset($_POST['recaptcha_token']);
     		$func_name = 'function_' . $_POST['form_func'];
     		$func_name($_POST);
    /*	} catch (Exception $e) {
    	    echo json_encode(array(
    			'status' => 'exception',
    			'reason' => 'Caught exception: '. $e->getMessage() . ' in file: '.$e->getFile() . ' at line: ' . $e->getLine()
    		));
    	}
	} else {
		echo json_encode(array(
			'status' => 'error',
			'reason' => "שגיאת קאפצ'ה"
		));
	}*/
	die();
}

function fixXSS($str){
    return htmlspecialchars($str);
}


function function_login() {
    //error_log("function_login");
	$email = fixXSS($_POST['username']);
	$pass = fixXSS($_POST['password']);
	
	$user = wp_authenticate($email, $pass); // בדיקה האם מייל וסיסמה תקינים
    
	if(!is_wp_error($user)) {
		$creds = array(
			'user_login'    => $email,
			'user_password' => $pass,
			'remember'      => true
		);
		$user  = wp_signon( $creds, false );
		echo json_encode( array(
			'status'   => 'success',
			'redirect' => get_site_url()
		) );

	} else {
	    if(username_exists($email)){
		    // אם הפרטים שהגולש הזין לא תקינים - חוזרת הודעת שגיאה
    		echo json_encode( array(
    			'status' => 'error',
    			'msg'    => 'שם המשתמש או הסיסמה אינם מזוהים'
    		) );
	    }
	    else{
	         // אם לא קיים משתמש במערכת - חוזרת הודעה על אפשרות רישום
    		echo json_encode( array(
    		    'status' => 'error',
    			'reason' => 'no_registration',
    			'msg'    => 'שם המשתמש או הסיסמה אינם מזוהים<br><br>פעם ראשונה באתר? לרישום נא ללחוץ על הירשם',
    			//'login_btn_msg'  => get_translation('first_entrance'),
    			//'registration_msg'  => get_translation('registration_msg'),
    		) );
	    }
	}
	die();

}

function check_recaptcha($token, $secret)
{
	$url = 'https://www.google.com/recaptcha/api/siteverify';
	//open connection
	$ch = curl_init();
	$sendtogoogle = 'secret=' . $secret . '&response=' . $token;

	//set the url, number of POST vars, POST data
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 2);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $sendtogoogle);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //return as string instead of default echo

	$result = curl_exec($ch);
	
	curl_close($ch);

	$obj = json_decode($result);
	
	//error_log('check_recaptcha: '.$result);
    //error_log('resukt= '. $obj->success);
	return $obj->success;
}


 ?>