<?php /* Template Name: Reset-Password */ ?>
    <main id="content" class="" role="main">
        <section class="reset-password">
 <?
 	 $error_msg ='';
    // קודם כל - בדיקה האם הקישור תקין ובתוקף
    $user = get_user_by('email', fixXSS($_GET['login']));
    if (!$user || is_wp_error($user)){
        //$error_msg = get_translation('error_msg_user_not_exists');
    } else {
        $user_info  = get_userdata( $user->ID );
        $user_login = $user_info->user_login;
        $check = check_password_reset_key(fixXSS($_GET['key']), $user_login);
        if ($check->get_error_code()) {
            $error_msg = 'הקישור לאיפוס הסיסמא אינו בתוקף יותר';
        }
    }
    if($error_msg) {
        echo "    
        <div class=' '>
            <div class ='reset_password_error_msg'>
                <div>$error_msg</div>
                <div><button id='reset_password_link_btn' class=''>שליחת קישור חדש</button></div>
            </div>".
        forgot_password_form().
        "</div>
        <script> jQuery('#reset_password_link_btn').click(function(){
            jQuery('.forgot-password-form').removeClass('d-none')
            jQuery('.reset_password_error_msg').addClass('d-none')
        });</script>";
        
    } else {
        // במידה והקישור אכן תקין - הצגת טופס איפוס סיסמה
        echo "
        <form class='site_form row' novalidate data-success='' data-failed=''>
            ". 
            "נא להכניס סיסמא חדשה<br> הסיסמא תשמש מעתה להתחברות לאתר".
            "
            <input type='hidden' name='form_func' value='reset_password' />
            <input type='hidden' id='user_login' name='user_id' value='" . $user->ID . "' autocomplete='off'/>
            <input type='hidden' name='rp_key' value='" . fixXSS($_REQUEST['key']) . "'/>
            ";
            wp_nonce_field( 'ajax-resetpassword-nonce', 'security' );
            ?>
            <label for="password1" class=""><? echo "סיסמא חדשה:" ?></label>
            <input class="textbox" type='password' name='password1'/>
            <label for="password2" class=""><?echo "אימות סיסמא חדשה"?>:</label>
            <input class="textbox" type='password' name='password2'/>
            <div id="form_error_msgs_container" ></div>
            <button type="submit" class="agron-button bold orange font-22"><? echo "איפוס סיסמה"?></button>
        </form>
        
        <div id="success_msg_of_form" class="d-none"><? echo "נשלח אליך קישור לאיפוס סיסמה לכתובת המייל שהזנת."?></div>
<?  } ?>
        </section>
    </main>

