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
add_action('wp_ajax_get_sub_table_checked_row', 'get_sub_table_checked_row');
function get_sub_table_checked_row()
{ 
    $sub_table= get_sub_table($_POST["page_name"],$_POST["parent_id_value"],$_POST["filter_value"]);
    echo( json_encode($sub_table) );
    die();
}
function get_sub_table($page_name,$parent_id_value,$filter_value_to_insert=null){
    global $actions_icons;
    error_log ('parent_id_value '.$parent_id_value." filter_value_to_insert ".$filter_value_to_insert);
    $page_info = STANDART_PAGES[$page_name]; 
    $result='
    <table class="data-grid">
        <thead>
            <tr class="header-row">';
                
    $join ="";
    $query = "SELECT ";
    foreach($page_info["sub_columns"] as $column){
        $query .= "wp_y1_".$page_info['sub_table'].".".$column["field"]. ", ";
        if(isset($column['join_table'])){
            $query .=($column['no_table_name']?"":  "wp_y1_".$column['join_table']."."). $column['join_value'].", ";
            if($column['no_table_name'])
                $column_to_insert = $column['join_value'];
            $join .= " LEFT JOIN wp_y1_".$column['join_table']." ON wp_y1_".$page_info['sub_table'].".".$column["field"] ." = wp_y1_" . $column['join_table'] . ".".$column['join_key'];
        }
        if(!isset($column["hidden"])){
        $result.='<th class="bold">'. $column["title"]??'' .'</th>';
        } 
    }
    $result.='<th class="bold"></th>';
    if(isset($page_info["actions"]) && is_array($page_info["actions"])) {
        $result.='<th class="bold"></th>';
    }
    $result.='</tr></thead>';
    $query = substr($query,0,-2);

    if(!is_null($filter_value_to_insert)){
        error_log ("filter_value_to_insert");

        $join_column= array_filter($page_info["sub_columns"], function ($var) { return (isset($var['join_table'])); });
        $join_column =reset($join_column);

        $query = "SELECT {$column_to_insert},100 as score";
        $query .= " FROM wp_y1_{$join_column['join_table']} join wp_y1_{$page_info["join_to_insert"]["table_name"]} 
        on wp_y1_{$join_column['join_table']}.{$join_column['join_key']} = wp_y1_{$page_info["join_to_insert"]["table_name"]}.{$page_info["join_to_insert"]["join_field"]} 
        where {$page_info["join_to_insert"]["filter_field"]} = ".$filter_value_to_insert;
    }
    else {
        error_log ("parent_id_value");
        $query .= " FROM  wp_y1_".$page_info['sub_table'];
        $query .= $join;
        $query .= " WHERE wp_y1_" . $page_info['sub_table'] . "." . $page_info['sub_table_field'] . " = " . $parent_id_value;
    }


    //error_log("query : ".$query);
    $data  = run_query($query); 
    //error_log("data : ".json_encode ($data));
    foreach($data as $row){
        $result.='<tr>';
        foreach($page_info["sub_columns"] as $column) {
            $field =isset($column['alias'])? $column['alias'] :(isset($column['join_table']) ?  $column['join_value'] : $column["field"]); 
            $list=  isset($column['list_name'])? constant($column['list_name']):null;
           
            if(!isset($column["hidden"]) ){
                $column_value =  isset($column['list_name']) && isset($list[$row->$field])?$list[$row->$field]: $row->$field; 
                $result.='<td>'. $column_value.'</td>';
            }
        } 
        $result.='<td></td>';
        if(isset($page_info["actions"]) && is_array($page_info["actions"])) {
            foreach($page_info["actions"] as $action) {
                $result.='<td class=""><span class="action" name="'. $action .'" onclick="action_func(this)"><i class="'. $actions_icons[$action].'"></i></span></td>';
            } 
        }
        $result.='</tr>';
    }
    $result.='</table>';
    return $result;
}
function get_tr_data($page_name, $data, $id_column){
    //error_log ("add_tr_data");
    global $actions_icons;
    $page_info = STANDART_PAGES[$page_name];
    $row = is_array ($data)? $data[0]:$data;
    //error_log ('row '.json_encode ($row));
    $html='<tr>
        <td data-id="checkbox" class="td-checkbox"><input type="checkbox" class="checkbox-row" value="'.$row->$id_column.'" id=""/></td>';
         foreach($page_info["columns"] as $column) {
            $field = isset($column['join_table']) ?  $column['join_value'] : $column["field"];
            $list = isset($column['list_name'])? constant($column['list_name']):null;

            if($field != $id_column){
                if($column['type']=="action"){
                    $column_value = '<span class="action" name="'.$column['field'].'" onclick="action_func(this)"><i class="'.$actions_icons[$column['field']].'"></i></span>';
                }else{
                    $column_value = isset($column['list_name']) && isset($list[$row->$field])?$list[$row->$field]: $row->$field;
                }
                $html .='<td>'. $column_value.'</td>';
            }
        }
         $html .='<td></td>';
         if(isset($page_info["actions"]) && is_array($page_info["actions"])) {
             foreach($page_info["actions"] as $action) {
                 $html .='<td class=""><span class="action" name="'.$action.'" onclick="action_func(this)"><i class="'. $actions_icons[$action].'"></i></span></td>';
            }
         }
    $html .='</tr>';
    //error_log ("add_tr_data enf ".$html);
    return $html;
}
function get_page_query($page_name,$filter_value =0)
{
    $page_info = STANDART_PAGES[$page_name];
    $join = "";
    $query = "SELECT ";
    foreach ($page_info["columns"] as $column) {
        if ($column["type"] == "action") continue;
        $query .= $column["field"] . ", ";
        if (isset($column['join_table'])) {
            $query .= "wp_y1_" . $column['join_table'] . "." . $column['join_value'] . ", ";
            $join .= " LEFT JOIN wp_y1_" . $column['join_table'] . " ON wp_y1_" . $page_info['table_name'] . "." . $column["field"] . " = wp_y1_" . $column['join_table'] . "." . $column['join_key'];
        }
    }
    $query = substr($query,0,-2);
    $query .= " FROM  wp_y1_".$page_info['table_name']. $join;
    if($filter_value!= 0){
        $query .= " WHERE ".get_id_column_in_page($page_name)." = ".$filter_value;
    }
    return $query ;
}
function get_id_column_in_page($page_name){
    $page_info = STANDART_PAGES[$page_name];
    $id_column = array_filter($page_info["columns"], function ($var) {
        return (isset($var['hidden']));
    });
    return $id_column[0]["field"];
}
$pages_in_site = array();
    $ar = array();
    $ar[] = array("field" => "material_id", "type" => "number", "hidden" => true,"primary_key"=>true);
    $ar[] = array("field" => "material_name", "type" => "text", "title"=>"חומר לימוד");
    $pages_in_site["material"] = array( "table_name"=>"material", "columns" => $ar,"actions"=>array("update","remove") ,"title" =>    "מגמות - חומר לימודי","singular"=>"מגמה","gender"=>2);
    //,"change-grouping","print","update","remove"
    $ar = array();
    $ar[] = array("field" => "student_code", "type" => "number", "hidden" => true,"primary_key"=>true);
    $ar[] = array("field" => "student_id", "type" => "text", "title"=>"תעודת זהות");
    $ar[] = array("field" => "last_name", "type" => "text", "title"=>"שם משפחה");
    $ar[] = array("field" => "first_name", "type" => "text", "title"=>"שם פרטי");
    $ar[] = array("field" => "stage", "type" => "text", "join_table" => "stage", "join_key" => "stage_id", "join_value" => "stage_name", "title"=>"שיעור");
    $ar[] = array("field" => "year_code", "type" => "year", "title"=>"שנה");
    $pages_in_site["students"] = array("table_name"=>"students", "columns" => $ar ,"actions"=>array("correcting-grade"), "title" => "תלמידים", "singular" => "תלמיד");
    
    $ar = array();
    $ar[] = array("field" => "staff_id", "title"=>"", "type" => "number", "hidden" => true,"primary_key"=>true);
    $ar[] = array("field" => "staff_name", "type" => "text","required" => "true", "title"=>"שם משפחה");
    $ar[] = array("field" => "first_name", "type" => "text", "title"=>"שם פרטי");
    $pages_in_site["staff"] = array("title" => "אנשי צוות", "table_name"=>"staff", "columns" => $ar,"singular"=>"איש צוות","actions"=>array("update","remove"));
    
    $ar = array();
    $ar[] = array("field" => "group_id", "type" => "number", "hidden" => true,"primary_key"=>true);
    $ar[] = array("field" => "group_name", "type" => "text", "title"=>"מגמה");
    $ar[] = array("field" => "group_time", "type" => "text","list_name" => "TIME_LIST", "title"=>"סדר");
    $ar[] = array("field" => "material", "type" => "number", "join_table" => "material", "join_key" => "material_id", "join_value" => "material_name", "title"=>"סוג מגמה");
    $ar[] = array("field" => "group_staff", "type" => "number", "join_table" => "staff", "join_key" => "staff_id", "join_value" => "staff_name", "title"=>"איש צוות");
    $ar[] = array("field" => "year_code", "type" => "year", "title"=>"שנה");
    $pages_in_site["groups"] = array("table_name"=>"grouping", "columns" => $ar,"title" => "קבוצות הלימוד","singular"=>"קבוצת לימוד","gender"=>2);
    
    $ar = array();
    $ar[] = array("field" => "test_id", "type" => "number", "hidden" => true,"primary_key"=>true);
    $ar[] = array("field" => "test_material", "type" => "text", "title"=>"הספק");
    $ar[] = array("field" => "test_subject", "type" => "text", "title"=>"מסכת");
    $ar[] = array("field" => "test_order", "type" => "number", "title"=>"סדר המבחנים");
    $ar[] = array("field" => "grouping_id", "type" => "number", "join_table" => "grouping", "join_key" => "group_id", "join_value" => "group_name", "title"=>"מגמה","on_selected"=>"fillStudentsToTest");
    $ar[] = array("field" => "test_date", "type" => "date", "title"=>"תאריך");
    $ar[] = array("field" => "test_type", "type" => "year","list_name" => "TEST_TYPE_LIST", "title"=>"סוג");
    $ar[] = array("field" => "semester", "type" => "text", "title"=>"שלישון");
//    $ar[] = array("field" => "update", "type" => "action", "text" => "לעידכון מבחן וציונים","title"=> "");
    
    $sub_columns = array();
    $sub_columns[] = array("field" => "id", "type" => "number", "hidden" => true,"primary_key"=>true);
    $sub_columns[] = array("field" => "test_id", "type" => "number", "hidden" => true);
    $sub_columns[] = array("field" => "student_id","alias" =>"student_name", "type" => "number", "join_table" => "students", "join_key" => "student_code", "join_value" => "CONCAT(last_name , ' ' , first_name) as student_name","no_table_name"=>true, "title"=>"שם התלמיד");
    $sub_columns[] = array("field" => "score", "type" => "number", "title"=>"ציון");

    $pages_in_site["tests"] = array("table_name"=>"test", "columns" => $ar,"sub_table"=>"scores","sub_table_title"=>"ציונים","sub_columns"=>$sub_columns,"sub_table_field"=>"test_id","title" => "מבחנים","singular"=>"מבחן"
    ,"actions"=>array("update"),"join_to_insert"=>array("table_name"=>"students_groupings","join_field"=>"student_id","filter_field"=> "group_id")
    );

    //$pages_in_site["tests"] = array("table_name"=>"test", "columns" => $ar,"title" => "מבחנים","singular"=>"מבחן");

    define("STANDART_PAGES",$pages_in_site);

    $time_list = array(1=>'בוקר', 2 =>'אחה"צ');
    define("TIME_LIST",$time_list);

    $test_type_list = array(1=>'שבועי', 2 =>'חודשי');
    define("TEST_TYPE_LIST",$test_type_list);

$actions_icons = array();
$actions_icons["remove"] ="fa-regular fa-trash-can";
$actions_icons["update"] ="fa-solid fa-circle-user";
$actions_icons["print"] ="fa-solid fa-print";
$actions_icons["correcting-grade"] ="fa-solid fa-file-circle-check";
$actions_icons["correcting-grade2"] ="fa-solid fa-file-arrow-up";
$actions_icons["correcting-grade3"] ="fa-solid fa-file-circle-exclamation";
$actions_icons["change-grouping"] ="fa-solid fa-person-walking-arrow-loop-left";
?>