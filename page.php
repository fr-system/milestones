<?php
    get_header(); 
    //custom_menu();
    $target = str_replace("/","",parse_url(get_page_link(), PHP_URL_PATH));
    $current = STANDART_PAGES[$target];
    $id_column = get_id_column_in_page($target);
?>

<main id="main" class="site-main" role="main">
     <? milestones_header() ?>
    <section class="padding-sides-40 stutents-list">
        <div class="flex-display margin-bottom-20 space-between">
            <h1 class="page-title darkblue font-40 bold"><? echo $current["title"]; ?></h1>
        </div>
        <div class="search-area box flex-display space-between margin-bottom-20 padding-sides-25">
            <div class="right flex-display">
                <div class="flex-display start ">
                    <input type="search" class="search" placeholder="<?echo "חיפוש"." ".$current["singular"]." "?>"/>
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <div class="filter-by center">
                    <div class="wrapper-button pointer">
                        <span>סינון</span>
                        <i class="filter-icon fa-solid fa-ellipsis-vertical"></i>
                    </div>
                    <ul class="wrapper-overlay list-filter-by" aria-label="סינון לפי:">
                        <li>שיעור</li>
                        <li>קבוצת לימוד</li>
                        <li>לימוד בוקר</li>
                        <li>מג"ש</li>
                    </ul>
                </div>
                

            </div>
            <div class="buttons-area center">
                <? echo get_button("print-button border-darkblue", "הדפסה","fa-solid fa-print")?>
                <? echo get_button("add-button background-azure", $current["singular"]." ". "חדש" . (isset($current["gender"]) && $current["gender"] == 2 ? "ה": "" ),"fa-solid fa-plus")?>
            </div>
        </div>
       
        <div class="content-area flex-display">    
            <div class="table-container central-table">
                <input type="hidden" name="page-name" value="<?echo $target?>"/>
                <input type="hidden" name="table_name" value="<?echo $current["table_name"]?>"/>
                <input type="hidden" name="id_column" value="<?echo $id_column?>"/>
                <table class="data-grid">
                    <thead>
                    <tr class="header-row">
                        <th class="bold td-checkbox"><input type="checkbox" class="choose-all-rows" id="all" value="all" name=""></th>
                        <?
                        foreach($current["columns"] as $column){
                            if($column["field"] != $id_column){
                            ?><th class="bold" name="<?=$column['field']?>"><?echo $column["title"]??""; ?></th>
                        <?} }?>
<!--                        <th class="bold"></th>-->
                        <? if(isset($current["actions"]) && is_array($current["actions"])) {?> 
                            <th class="bold" name="actions"></th><?}?>
                    </tr>
                    </thead>
                     <?php
                    $query = get_page_query($target);
                    $page_data  = run_query($query);
                    foreach($page_data as $row){
                        echo get_tr_data($target,$row,$id_column);
                        ?>
                    <?}
                    ?>
            </table>
            </div>
            <div class="new-row flex-part-50 margin-r-50">
                <div class="dark-blue margin-bottom-40 explanation"></div>
                <div class="grid-container margin-bottom-40">
                    <input type="hidden" name="action" value=""/>
                    <input type="hidden" name="id_column" value=""/>
                    <?
                    foreach($current["columns"] as $column){
                        if( $column["type"]=="action")continue;
                        if(!isset($column["primary_key"]) || $column["primary_key"]!=true ){
                    ?>
                            <label class="label-for" for="<?echo $column["field"]?>"><?echo $column["title"]?><?echo isset($column["required"])? " *" : "" ?></label>
                            <?if(isset($column["join_table"])|| isset($column["list_name"])){?>
                                <select name="<?echo $column["field"]?>" value="" <?= isset($column["on_selected"]) ?"onchange='{$column["on_selected"]}(this)'" :""?>>
                                    <?  create_combo_options($column);  ?>
                                </select>
                            <?}
                            else{?>
                            <input class="" name="<?echo $column["field"]?>" value="" <? echo (isset($column["required"]) ? "required" : "" )?> />
                            
                        <?  }
                      }
                    }
                    ?>    
                </div>
                <div class="buttons-area">
                    <? echo get_button("save-button background-azure", "שמור","fa-solid fa-check")?>
                    <? echo get_button("cancel-button border-darkblue", "בטל","fa-solid fa-xmark")?>
                </div>

            </div>
            <?if(isset($current["sub_table"])){?>
                <div class="table-container margin-r-50" id="sub-table-container">
                    <h3 class="darkblue font-20 bold text-center"><?echo $current['sub_table_title']?></h3>
                <input type="hidden" name="table_name" value="<?echo $current["sub_table"]?>"/>
                <? $sub_id_column = array_filter($current["sub_columns"], function ($var) {
                    return (isset($var['primary_key']));
                });
                $sub_id_column = $sub_id_column[0]["field"];
                echo get_sub_table($target,$page_data[0]->$id_column);
                ?>
            </div>
            <?}?>
        </div>
       
    <script type="text/javascript">
    
        function action_func(e){
            var action = jQuery(e).attr("name");
            var row = jQuery(e).parent().parent();
            if(action == "remove"){
                row.find('.checkbox-row').prop('checked', true);
                var message = "האם למחוק את השורה שבחרת?";
                show_slider_message("aaa",message,"removeRows");
            }
            else if(action == "update" || action == "edit"){
                showRowEditing("update","<? echo $current["singular"]?>","<? echo isset($current["sub_table"])?>");
                jQuery(".new-row input[name=id_column]").val(row.find("td.td-checkbox input").val());
                var inputs = jQuery(".new-row input:not([type=hidden]) , .new-row select");
                var values = jQuery.map(row.find("td:not(.td-checkbox)"),function(td){
                    return jQuery(td).text();
                });
                
                for(var i = 0; i < inputs.length; i++){
                    text = values[i];
                    input = inputs[i];
                    if(input.localName =="select"){
                        var selected = jQuery.grep(input.options,function(option) {return option.text == text});
                        if(selected.length >0){
                            input.selectedIndex = selected[0].index;
                        }
                    }
                    if(input.localName =="input"){
                        jQuery(input).val(text);
                    }
                }
                if("<? echo (isset($current["sub_table"]) ? $current["sub_table"] : "") ?>" == "scores"){
                    get_sub_table_checked_row();
                }
            }
            
        }
        
        function showRowEditing(action,singular,subTable){
            jQuery(".new-row").show();
            var columns =  jQuery(".central-table th");
            jQuery(".central-table td:nth-child("+columns.length+"),.central-table th:nth-child("+columns.length+") ").hide();
            if(subTable){
                jQuery("#sub-table-container").show();
                jQuery(".search-area").hide();
                jQuery(".table-container.central-table").hide();
                jQuery("#sub-table-container").find("table").find("tbody").empty();
            }
            else{
                jQuery(".table-container.central-table").addClass("flex-part-70");
            }
            jQuery(".new-row input[name=action]").val(action);
            var text2 = "ולאחר מכך ללחוץ על שמור";
            
            if(action == "insert"){
                var text1="נא להכניס פרטי ";
            }
            else if(action == "update" || action == "edit"){
                var text1="נא לעדכן פרטי ";
            }
            jQuery(".new-row .explanation").text(text1 + singular + " " + text2);
        }

        function hideRowEditing(){
            jQuery(".new-row").hide();
            jQuery(".table-container.central-table").show();
            jQuery(".search-area").show();
            jQuery(".table-container.central-table").removeClass("flex-part-70");
            jQuery("#sub-table-container").hide();

            var columns =  jQuery(".central-table th");
            jQuery(".central-table td:nth-child("+columns.length+"),.central-table th:nth-child("+columns.length+") ").show();
        }
        function getInputsValue(){
            var options = {};
            
            var inputs = jQuery(".new-row input:not([type=hidden]) ,.new-row select");
            options.values =jQuery.map(inputs,function(row){return "'" + jQuery(row).val() + "'" });
            <? $columns = array_filter($current["columns"], function ($var) { return (!isset($var['primary_key'])); });?>
            var columns ="<? echo implode(",", array_column($columns, 'field') )?>";
            options.columns = columns.split(',');
            options.tableName ="<?echo $current['table_name']?>";
            return options;
        }
        
      /*  function addNewRowToTable(){
            var inputs = jQuery(".new-row input:not([type=hidden]) ,.new-row select");
            values =jQuery.map(inputs,function(row){return jQuery(row).val() });
            var newRow= '<tr> <td data-id="checkbox" class="td-checkbox"><input type="checkbox" class="checkbox-row" value="<?//echo $row->$id_column?>" id=""/></td>'
            jQuery.each(values,function(key,column_value){
                newRow += '<td>' + column_value+'</td>'
            });
            newRow += '<td></td></tr>'
            jQuery("tbody").append(newRow)
        }*/
        
        jQuery(".add-button").click(function(){
            showRowEditing("insert","<? echo $current["singular"]?>","<? echo isset($current["sub_table"])?>");
            jQuery(".new-row input:not([type=hidden])").each(function (i,input){
                jQuery(input).val("");
            })
            jQuery(".new-row select").each(function (i,select){
                select.selectedIndex=-1;
            })
        });
        
        jQuery(".save-button").click(function(){
            var options = getInputsValue();
            action = jQuery(".new-row input[name=action]").val();
            if(action == "insert"){
                var sql = "insert into wp_y1_" + options.tableName + " (" + options.columns.join(",") + ") values(" + options.values.join(",") + ")";
            }
            else if(action == "update" || action == "edit"){
                var sql = "update wp_y1_" + options.tableName + " set " ;
                for(var i = 0; i < options.columns.length; i++){
                    if(i > 0){
                        sql += " , ";
                    }
                    sql += options.columns[i] + "=" + options.values[i];
                }
                sql += " where " + jQuery('input[name=id_column]').val() + " = " + jQuery(".new-row input[name='id_column']").val();
            }
            idValue = jQuery(".new-row input[name='id_column']").val();
            var sql_arr =[{query:sql,update_table:'<?=$target?>',values:[idValue]}];
            <?php
            if(isset($current["sub_table"])){
                $sub_columns= array_filter($current["sub_columns"], function ($var) { return (!isset($var['primary_key']) && !isset($var['query_field'])); });
                $sub_columns= array_column($sub_columns, 'field');
                $columns_names =  implode(",",$sub_columns  );

                $sql= "insert into wp_y1_{$current["sub_table"]} ({$columns_names}) VALUES  ";
                //$sql="select ";
//                $join_column= array_filter($sub_columns, function ($var) { return (isset($var['join_table'])); });
//                $join_column =reset($join_column);
                //error_log ('joim column '.json_encode ( $join_column));
            //                $continue_sql = ",{$join_column['join_key']},100 from wp_y1_{$join_column['join_table']} join wp_y1_{$current["join_to_insert"]["table_name"]} on wp_y1_{$join_column['join_table']}.{$join_column['join_key']} = wp_y1_{$current["join_to_insert"]["table_name"]}.{$current["join_to_insert"]["join_field"]} where {$current["join_to_insert"]["filter_field"]} = ";

                ?>
            var values ="";
            jQuery("#sub-table-container tbody tr").each(function (i,tr){
                tr= jQuery(tr);
                if(i>0) { values+=","; }
                values+="(";
            <?php
            foreach ($sub_columns as $i=>$column_name) {
                if($i>0){?> values+=","; <?}?>
                if(tr.find("td[name=<?=$column_name?>]").has('input').length>0){
                    values+=tr.find("td[name=<?=$column_name?>] input").val() =='' ?  "0":tr.find("td[name=<?=$column_name?>] input").val();
                }
                else if (tr.find("td[name=<?=$column_name?>]").text()!= ""){
                    values += tr.find("td[name=<?=$column_name?>]").text();
                }
                else{
                    values +='[<?=$column_name?>]';
                }

                <?}?>
                values+=")";
            })
                // whereValue = jQuery(".new-row select[name='grouping_id']").val();
                sql = "<?= $sql?>" + values ;
                //ajaxfunction('run_sql', sql,{update_table:'<?=$target?>',sub_table_value:idValue});
                sql_arr.push({query:sql,update_table:'<?=$target?>'/*,sub_table_value:idValue*/})
                <?php
             }?>
            ajaxfunction('run_sql', sql_arr);
            hideRowEditing();

        });
        jQuery(".cancel-button").click(function(){
            hideRowEditing();
        });
        
        jQuery(".search").on("search", function() {
            var value = jQuery(this).val();
        
            jQuery("table tr").each(function(index) {
                if (index != 0) {
                    row = jQuery(this);
                    var found = false;
                    row.find("td").each(function(index, td) {
                        var text = jQuery(td).text();
                        if(text && text.includes(value)){
                            found = true;
                        }
                    })
                    if (!found) {
                        row.hide();
                    }
                    else {
                        row.show();
                    }
                }
            });
        });
        
        jQuery(".list-filter-by li").click(function(){
            /*TODO filter by*/
        });


        function fillStudentsToTest(a){
            var b =jQuery(a);
            var v =b.val();
            get_sub_table_checked_row(v);
            <?php
          /*  $sub_columns= array_filter($current["sub_columns"], function ($var) { return (!isset($var['primary_key'])); });
            $columns_names =  implode(",", array_column($sub_columns, 'field') );
            //$sql= "insert into wp_y1_{$current["sub_table"]} ({$columns_names}) select ";
            $sql="select ";
            $join_column= array_filter($sub_columns, function ($var) { return (isset($var['join_table'])); });
            $join_column =reset($join_column);
            //error_log ('joim column '.json_encode ( $join_column));
            $continue_sql = ",{$join_column['join_key']},100 from wp_y1_{$join_column['join_table']} join wp_y1_{$current["join_to_insert"]["table_name"]} on wp_y1_{$join_column['join_table']}.{$join_column['join_key']} = wp_y1_{$current["join_to_insert"]["table_name"]}.{$current["join_to_insert"]["join_field"]} where {$current["join_to_insert"]["filter_field"]} = ";
                */?>/*
            whereValue = jQuery(".new-row select[name='grouping_id']").val();
            sql = "<?php /*= $sql*/?>" + idValue + "<?php /*= $continue_sql*/?>" + whereValue;
            ajaxfunction('run_sql', sql,{update_table:'<?php /*=$target*/?>',sub_table_value:idValue});*/
        }
	</script>
  </section>
</main>
<?
function create_combo_options($column){
    if(isset($column['join_table'])){
        $query = "SELECT {$column['join_key']} as key_field,{$column['join_value']} as value_field FROM wp_y1_{$column['join_table']}";
        $option_list  = run_query($query);
    }
    else if (isset($column['list_name'])){
        $option_list  =constant( $column['list_name']);
    }
    
    foreach($option_list as $key=>$option){
        if(isset($column['join_table'])){
        echo "<option value='{$option->key_field}'>{$option->value_field}</option>";
        }
        else if (isset($column['list_name'])){
        echo "<option value='{$key}'>{$option}</option>";
        }
    }
}
?>
<?php get_sidebar( 'content-bottom' ); ?>

<?php get_sidebar(); ?>
<?php //get_footer(); ?>