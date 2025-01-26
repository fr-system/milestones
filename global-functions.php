<?php

function current_user(){?>
    <div class="flex-display items-center flex-display padding-sides-25 dark-blue space-between">
        <i class="fa-solid fa-circle-user flex-part-20"></i>
        <span class="flex-part-50 bold" >ישיבת חידושי הרים</span>
        <i class="fa fa-caret-down"></i>
    </div><?
}
function milestones_header(){
    ?>
    <div class ="page-header background-white dark-blue flex-display margin-bottom-20 padding-sides-40 space-between">
        <div class="center"></div>
        <div class="flex-display center">
            <div class="padding-sides-25" id="logout">התנתקות</div>
            <div class="padding-sides-25"></div>
            <? current_user(); ?>
        </div>
    </div> 
    <?
}
function get_button($class_name, $text, $icon = null){
    ?>
    <button class="<?echo $class_name?>"><span class="button-inner" >
    <? if($icon != null){?>
        <i class="<?echo $icon?>"></i>
        <?}?>
            <span><?echo $text?></span></span>
        </button>
    <?
}
function popup_window(){
    ?>
        <div class="popup_page_overlay">
            <div class="popup">
                <div class="close-popup"><i class="fa-solid fa-xmark"></i></div>
                <div class="content">
                    
                </div>
            </div>
        </div>
    <?
}
function slider_message(){//<i class="fa-solid fa-circle-exclamation orange" style="font-size: xxx-large;"></i>
    ?>
    <div class="slider-message">
        <div class="content">
            <h1 class="dark-blue"></h1>
            <h3 class="secondary-text"></h3>
        </div>
        <div class="buttons-area flex-display items-center">
            <? echo get_button("ok-button background-azure", "אישור","fa-solid fa-check")?>
            <? echo get_button("cancel-button border-darkblue", "בטל","fa-solid fa-xmark")?>
        </div>
    </div>
    <?
}
?>