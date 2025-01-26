<?php /* Template Name: איתור קבוצה */ ?>

<? $time_arr = array("בוקר","אחה\"צ"); ?>
<?php get_header();  ?>
<!--<div id="primary" class="content-area">-->
<!--   -->
    <main id="main" class="site-main" role="main">
        <? milestones_header() ?>
      <section class="grouping-locator">
      <div class="grid-container col-4" >
        <div class="staff">
            <span>איש צוות</span>
            <?php 
            $query = "SELECT * FROM wp_y1_staff order by staff_name";
            $staff_list  = run_query($query); 
            //error_log("result: ". print_r( $result));?>
           <select name="staff[]" class="staff-filter"> 
            <? foreach($staff_list as $staff){?>
                <option value ="<? echo $staff->staff_id; ?>" ><? echo $staff->staff_name ." ". $staff->first_name; ?> </option>
            <? }?>
            </select> 
                
        </div>
        <div class="grouping">
            <span>מגמה</span>
            <?php 
            $query = "SELECT * FROM wp_y1_material order by material_name";
            $result  = run_query($query); 
            //error_log("result: ". print_r( $result));?>
           <select name="material[]" class="material-filter"> 
            <? foreach($result as $row){?>
                <option value ="<? echo $row->material_id; ?>" ><? echo $row->material_name; ?> </option>
            <? }?>
            </select> 
                
        </div>
        <div class="time">
            <span>סדר</span>
           <select name="time[]" class="time-filter"> 
            <? foreach($time_arr as $key=>$value){?>
                <option value ="<? echo $key+1; ?>" ><? echo $value; ?> </option>
            <? }?>
            </select> 
                
        </div>
        <div class="stage">
            <span>שיעור</span>
            <?php 
            $query = "SELECT * FROM wp_y1_stage order by stage_id";
            $result  = run_query($query); 
            //error_log("result: ". print_r( $result));?>
           <select name="stage[]" class="stage-filter"> 
            <? foreach($result as $row){?>
                <option value ="<? echo $row->stage_id; ?>" ><? echo $row->stage_name ; ?> </option>
            <? }?>
            </select> 
                
        </div>
      </div>
      <div>
        <div class="stage">
            <span>מגמות</span>
            <?php 
            $query = "SELECT * FROM wp_y1_grouping join wp_y1_staff on wp_y1_grouping.group_staff = wp_y1_staff.staff_id order by group_id";
            $result  = run_query($query); ?>
            <div class ="grouping-grid"></div>
            <script>
            /*var js_data = '<?php echo json_encode($result); ?>';
            var js_obj_data = JSON.parse(js_data );
            var grid = jQuery(".grouping-grid").kendoGrid({
                dataSource: new kendo.data.DataSource({data:js_obj_data}),
                columnMenu:  false,
                height: 680,
                selectable: "multiple row",
 
                //dataBound: onDataBound,
                //toolbar: ["excel", "pdf", "search"],
                columns: [{
                    selectable: true,
                    width: 75,
                    attributes: {
                        "class": "checkbox-align",
                    },
                    headerAttributes: {
                        "class": "checkbox-align",
                    }
                }, {
                    field: "group_id",
                    title: "מספור",
                    width: 75
                }, {
                    field: "group_name",
                    title: "שם המגמה",
                    width: 140
                }, {
                    field: "group_time",
                    title: "סדר",
                    //template: "<span id='badge_#=ProductID#' class='badgeTemplate'></span>",
                    width: 130,
                }, {
                    field: "staff_name",
                    title: "איש צוות",
                    template: "<span>#=staff_name# #=first_name#</span>",
                    width: 125
                },
                { command: "destroy", title: "&nbsp;", width: 120 }],
            });
            */
            </script>
           <table name="grouping[]" class="grouping-grid"> <thead><td>מספר </td><td>שם</td><td>סדר</td><td>איש צוות</td></thead>
            <? foreach($result as $row){?>
                <tr data-stage="<? //echo $row->group_stage; ?>" data-time="<? echo $row["group_time"]; ?>" data-class="<? echo $row["group_class"]; ?>" data-staff="<? echo $row["roup_staff"]; ?>" class="<? echo $row["group_stage"]; ?>">
                    <td> <? echo $row["group_id"]; ?> </td>
                    <td> <? echo $row["group_name"] ; ?> </td>
                    <td> <? echo $time_arr[$row["group_time"]-1] ; ?> </td>
                    <td> <? echo $row["staff_name"] ." ". $row["first_name;"] ?> </td>
                </tr>
            <? }?>
            </table>
                
        </div>
<!--      </div>-->
      <script type="text/javascript">

	    (function(){
	        jQuery("select").change(function(){
	            //var filter ="[data-stage="+this.value+"]";
	            var filter ="[data-"+this.name.replace("[]","") +"="+this.value+"]";
	            var table = jQuery(".grouping-grid").find("tbody");
	            jQuery.each(jQuery(table).find("tr"),function(k,tr){
	                tr=jQuery(tr);
	                if(tr.is(filter)  ){
	                    tr.css("display","table-row");
	                }
	                else{
	                    tr.css("display","none");
	                }
	                
	            });
	            this.name;
	        });
	    }())
	   </script>
      </section>
    </main><!-- .site-main -->
 
    <?php get_sidebar( 'content-bottom' ); ?>
 
</div><!-- .content-area -->
 
<?php get_sidebar(); ?>
<?php get_footer(); ?>