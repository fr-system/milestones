<?php /* Template Name: login */ ?>
<?php wp_head(); ?>
<div id="primary" class="login-container flex-display">
    <section class="login-section background-gray flex-display center">
        <form id="login_form" class="site_form flex-display column text-right center">
            <input type="hidden" name="form_func" value="login">
            <h2 class="darkblue bold font-40 margin-bottom-40 text-center">שלום וברוך הבא!</h2>
            <label for="username" class="sr-only"></label>
            <input required="" type="text" class="text-box margin-bottom-40" name="username" id="username" value="" placeholder="* מייל">
            <label for="form_password_84" class="sr-only"></label>
            <input required="" type="password" class="text-box margin-bottom-40" name="password" id="form_password_84" value="" placeholder="* סיסמא">
            <div class="flex-display space-between margin-bottom-20">
                <button type="submit" class="btn-login bold background-lightblue flex-part-45">התחבר</button>
            </div>
            <div class="">
            <span class="white">שכחת את הסיסמא?</span>
            <a class="lightblue" id="login_forgot_password_btn">לאיפוס סיסמא</a>
            </div>
        </form>
   </section>
</div>

<?
     //email           <button type="submit" class="btn-registration bold background-darkblue flex-part-45">הירשם</button>


function function_register() {
	$email = fixXSS($_POST['username']);
	$pass = fixXSS($_POST['password']);
    $user_id = email_exists($email);
    $user_id =  $user_id ? $user_id : username_exists($email);
    if($user_id) {}//not usually,  if user exist as manager  by agron and not reader
    else{ // Usually, register reader as new user in site
        $userdata = array(
            'user_login'        =>  $email,
            'user_pass'         =>  $pass,
            'user_email'        =>  $email,
            'display_name'      => $reader->firstname.' ' .$reader->lastname ,
            'first_name'        => $reader->firstname,
            'last_name'         => $reader->lastname, 
            'role'              => 'subscriber',
            );
       
	    $user_id = wp_insert_user( $userdata ) ;
	    $new_user = true;
    }
	    
    if(is_wp_error($user_id)) {
        echo json_encode( array(
			'status'   => 'faild',
			'msg'      => $user_id->get_error_message(),
		));
    }
    else{
		$creds = array(
			'user_login'    => $email,
			'user_password' => $pass,
			'remember'      => true
		);
		$user  = wp_signon( $creds, false );
		if(isset($new_user)){
            //setcookie('registration_success', get_translation("registration_success_title") ."|". get_translation("registration_success_msg"), time() + (60*1), "/");
		}
	    echo json_encode( array(
			'status'   => 'success',
		));
    }
	die();
}

function get_reset_password_page(){
    return get_site_url().'/reset-password';
}

function function_send_password_reset_link() {

	check_ajax_referer( 'ajax-lostpass-nonce', 'security' );

	$user_email = sanitize_text_field( $_POST['email'] );

	$user = get_user_by( 'email', $user_email );
	if ( $user instanceof WP_User ) {
		$user_id    = $user->ID;
		$user_info  = get_userdata( $user_id );
		$unique     = get_password_reset_key( $user_info );
		$unique_url = get_reset_password_page() . "?action=reset_pass&key=$unique&login=" . rawurlencode( $user_email );

		$subject = get_translation( 'reset_password_email_subject' ). " " .get_option('library_name');
		$message = str_replace(
			array(  '%link%', '%name%' ),
			array("<a href='$unique_url'>$unique_url</a>", get_user_display_name($user) ),
			get_translation( 'reset_password_email_text' ) );
	

		$headers = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
		$headers .= "From: אגרון <info@library.org.il> \r\n";
		$headers .= "Reply-To: אגרון <info@library.org.il> \r\n";
		$to = $user_email;
        $message = '<table style="direction: rtl;">
                        <tbody style=" padding: 20px; font-family:afek; font-size:25px;">
                        <tr style="background-color:#0D116F; heigth: 200px"></tr>
                        <tr><td>
                        <div style=" color: #0D116F;">
                            <span style="font-weight: 700; color:#FF7A17;">'.get_user_display_name($user).',</span><br>
                            '.get_translation("reset_password_email_content").'<br><br>
                            <a style="background-color:#ee6d0c; color:white; padding: 10px; text-decoration: none; font-weight: 700;" href="'.$unique_url.'">
                            '.get_translation("new_password").'
                            </a>
                        </div>
                        </td></tr></tbody>
                    </table>';

		$ok = wp_mail( $to, $subject, $message, $headers );
		echo json_encode( array(
			'status' => 'success',
		) );
	} else {

		echo json_encode( array(
			'status' => 'error',
			'msg'    => get_translation("user_not_exist")
		) );
	}
}

function function_reset_password() {
	check_ajax_referer( 'ajax-resetpassword-nonce', 'security' );
	
	$pass    = fixXSS( $_POST['password1'] );
	$secpass = fixXSS( $_POST['password2'] );
	
	// בדיקה שאכן הזינו את שתי השדות
	if ( ! $pass || ! $secpass ) {
		die( json_encode( array(
			'status' => 'error',
			'msg'    => get_translation("entering_double_new_password")
		) ) );
	}
	// בדיקה שהשדות זהים
	if ( $pass != $secpass ) {
		die( json_encode( array(
			'status' => 'error',
			'msg'    => get_translation("passwords_entered_not_same")
		) ) );
	}

	$user_info  = get_userdata( fixXSS($_POST['user_id'] ));
	$user_login = $user_info->user_login;
	$user = check_password_reset_key( fixXSS( $_POST['rp_key'] ), $user_login );

	if ( $user instanceof WP_User ) {
		wp_set_password( $pass, $user->ID );

		echo json_encode( array(
			'status'   => 'success',
			'className' =>'login',
		) );
	} else {

		echo json_encode( array(
			'status' => 'error',
			'msg'    => get_translation("password_reset_failed")
			) );
	}
}
?>
