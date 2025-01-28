<?php
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
    foreach($page_info["sub_table"]["columns"] as $column){
        if(isset($column['join_table'])){
            if(isset($column['query_field'])){
                $column_to_insert = $column['query_field'] ." as ". $column['field'];
            }
            $query .= (isset($column['query_field'])?$column['query_field'] ." as ":  "wp_y1_".$column['join_table']."."). $column['field'].", ";

            $join .= " LEFT JOIN wp_y1_".$column['join_table']." ON wp_y1_".$page_info["sub_table"]["table_name"].".".$column["foreign_key"] ." = wp_y1_" . $column['join_table'] . ".".$column['join_key'];
        }
        else{
            $query .= "wp_y1_".$page_info["sub_table"]["table_name"].".".$column["field"]. ", ";
        }
            $result.='<th name="'.$column["field"].'" class="bold '.(isset($column["hidden"])? 'hidden':''). '">'. (isset($column["title"])?$column["title"]:'') .'</th>';
        //"
    }
    $result.='<th class="bold"></th>';
    if(isset($page_info["sub_table"]["actions"]) && is_array($page_info["sub_table"]["actions"])) {
        $result.='<th class="bold"></th>';
    }
    $result.='</tr></thead>';
    $query = substr($query,0,-2);

    if(!is_null($filter_value_to_insert)){
        //error_log ("filter_value_to_insert");
        $join_column= array_filter($page_info["sub_table"]["columns"], function ($var) { return (isset($var['join_table'])); });
        $join_column =reset($join_column);
        $query = "SELECT {$parent_id_value} as test_id, wp_y1_{$join_column['join_table']}.student_code as student_id,  {$column_to_insert}";
        $query .= " FROM wp_y1_{$join_column['join_table']} join wp_y1_{$page_info["sub_table"]["join_to_insert"]["table_name"]} 
        on wp_y1_{$join_column['join_table']}.{$join_column['join_key']} = wp_y1_{$page_info["sub_table"]["join_to_insert"]["table_name"]}.{$page_info["sub_table"]["join_to_insert"]["join_field"]} 
        where {$page_info["sub_table"]["join_to_insert"]["filter_field"]} = ".$filter_value_to_insert;
        error_log ("sql ".$query);
    }
    else {
        //error_log ("parent_id_value");
        $query .= " FROM  wp_y1_".$page_info['sub_table']["table_name"];
        $query .= $join;

        $query .= " WHERE wp_y1_" . $page_info['sub_table']["table_name"] . "." . $page_info['sub_table']['field'] . " = " . $parent_id_value;
        if(isset($page_info["sub_table"]["filter"])){
            $query .= " AND " .$page_info["sub_table"]["filter"];
        }
    }
    //error_log("query : ".$query);
    $data  = run_query($query);
    foreach($data as $row){
        $result.='<tr class="'.(is_null($filter_value_to_insert)?'':'dirty').'">';
        foreach($page_info['sub_table']["columns"] as $column) {
            $field = $column["field"];
            $list = isset($column['list_name'])? constant($column['list_name']):null;
            $column_value = isset($column['list_name']) && isset($list[$row->$field])?$list[$row->$field]:(isset($row->$field)? $row->$field:'');
            $result.='<td name="'.$field.'"  class="'.(isset($column["hidden"])? 'hidden':''). '">';
                if(!is_null($filter_value_to_insert) && isset($column["input"])){
                    $result.='<input type="number" />';
                }
                else {
                    $result.=$column_value;
                }
                $result.='</td>';
        }
        if(isset($page_info['sub_table']["actions"]) && is_array($page_info['sub_table']["actions"]) && is_null($filter_value_to_insert)) {
            foreach($page_info['sub_table']["actions"] as $action) {
                $result.='<td class=""><span class="actions" name="'. $action .'" onclick="action_func(this)"><i class="'. $actions_icons[$action].'"></i></span></td>';
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
                $column_value = '<button  class="action bg-lightblue" name="'.$column['field'].'" onclick="action_func(this)"><i class="'.$actions_icons[$column['field']].'"></i><span>פעולה</span></button>';
            }else{
                $column_value = isset($column['list_name']) && isset($list[$row->$field])?$list[$row->$field]: $row->$field;
            }
            $html .='<td>'. $column_value.'</td>';
        }
    }
    //$html .='<td></td>';
    if(isset($page_info["actions"]) && is_array($page_info["actions"])) {
        $html .='<td class="flex-display space-around">';
        foreach($page_info["actions"] as $action) {
            $html .='<button  class="action bg-lightblue" name="'.$action.'" onclick="action_func(this)">               
                 <i class="'. $actions_icons[$action].'"></i><span>פעולה</span></button>';
        }
        $html .='</td>';
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
$pages_in_site["material"] = array( "table_name"=>"material", "columns" => $ar,"actions"=>array("edit","remove") ,"title" =>    "מגמות - חומר לימודי","singular"=>"מגמה","gender"=>2);
//,"change-grouping","print","update","remove"
$ar = array();
$ar[] = array("field" => "student_code", "type" => "number", "hidden" => true,"primary_key"=>true);
$ar[] = array("field" => "student_id", "type" => "text", "title"=>"תעודת זהות");
$ar[] = array("field" => "last_name", "type" => "text", "title"=>"שם משפחה");
$ar[] = array("field" => "first_name", "type" => "text", "title"=>"שם פרטי");
$ar[] = array("field" => "stage", "type" => "text", "join_table" => "stage", "join_key" => "stage_id", "join_value" => "stage_name", "title"=>"שיעור");
$ar[] = array("field" => "year_code", "type" => "year", "title"=>"שנה");
$pages_in_site["students"] = array("table_name"=>"students", "columns" => $ar ,"actions"=>array("update","correcting-grade"), "title" => "תלמידים", "singular" => "תלמיד");

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
$pages_in_site["groups"] = array("table_name"=>"grouping", "columns" => $ar,"title" => "קבוצות הלימוד","singular"=>"קבוצת לימוד","gender"=>2,"actions"=>array("edit","remove"));

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
$sub_columns[] = array("field" => "student_id", "type" => "number", "hidden" => true);
$sub_columns[] = array("field" => "student_name","query_field"=>"CONCAT(last_name , ' ' , first_name)", "type" => "text", "join_table" => "students", "join_key" => "student_code","foreign_key" => "student_id","no_table_name"=>true, "title"=>"שם התלמיד");
$sub_columns[] = array("field" => "score", "type" => "number", "title"=>"ציון","input"=>true);
$sub_columns[] = array("field" => "old", "type" => "number","query_field"=>"(select score from wp_y1_scores where )", "title"=>"ציון קודם");
/*SELECT scores.*,old_scores.score as old_score FROM
(SELECT * FROM `wp_y1_scores` WHERE `old` =0) as scores left join
(SELECT * FROM `wp_y1_scores` WHERE `old` =1) as old_scores on old_scores.`test_id` = scores.`test_id` and old_scores.`student_id` = scores.`student_id`*/

$sub_table = array("table_name"=>"scores","title"=>"ציונים","columns"=>$sub_columns,"field"=>"test_id"
,"join_to_insert"=>array("table_name"=>"students_groupings","join_field"=>"student_id","filter_field"=> "group_id"),
    "actions"=>array("new-score"),"singular"=>"ציון","filter"=>"old = 0");

$pages_in_site["tests"] = array("table_name"=>"test", "columns" => $ar,"title" => "מבחנים","singular"=>"מבחן"
,"actions"=>array("edit"),"sub_table"=>$sub_table
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
$actions_icons["new-score"] ="fa-solid fa-circle-user";
$actions_icons["edit"] ="fa-solid fa-pencil";
$actions_icons["print"] ="fa-solid fa-print";
$actions_icons["correcting-grade"] ="fa-solid fa-file-circle-check";
$actions_icons["correcting-grade2"] ="fa-solid fa-file-arrow-up";
$actions_icons["correcting-grade3"] ="fa-solid fa-file-circle-exclamation";
$actions_icons["change-grouping"] ="fa-solid fa-person-walking-arrow-loop-left";
?>