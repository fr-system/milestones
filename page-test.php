<?php 
    get_header(); 

    $student_code = 58;
    $query = "SELECT * FROM wp_y1_students where student_code = " .$student_code;
    $student  = run_query($query)[0]; 

?>

<main id="main" class="site-main" role="main">
    <? milestones_header(); ?>
    <section class="padding-sides-40">
        <div class="flex-display margin-bottom-20 space-between">
            <h1 class="page-title darkblue font-40 bold"><? echo "מבחן חדש"; ?></h1>
        </div>
        
         <div class="box flex-display space-between margin-bottom-40 padding-sides-25">
            <div class="right flex-display">
                <div class="flex-display start ">
                    <input type="search" class="search" placeholder="<?echo "חיפוש מגמה"?>"/>
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
            </div>
        </div>
        <div class="content-area">
            <div class="tabs margin-bottom-40">
                <div class="menu flex-display">
                    <div class="tablinks active" data-tab="1" onclick="openTab(event)">מגמות</div>
                    <div class="tablinks" data-tab="2" onclick="openTab(event)">מבחנים</div>
                    <div class="tablinks" data-tab="3" onclick="openTab(event)">דוחות</div>
                </div>
                <div class="tab-content">
                    <div class="tabcontent box" style="display: block" data-tab="1">
                        <? $rows = array();
                        $rows[] = array("מגמה בוקר","גרש תוספות");
                        $rows[] = array("מגמת אחהצ","דף יומי");
                        $rows[] = array("מגמת שינון","מסכת ברכת ושבת");
                        foreach($rows as $row){ ?>
                            <div class="wrapper flex-display">
                                <div class="clause flex-display flex-part-30">
                                    <div class="label-value flex-part-70">
                                        <span class="label lightblue bold"><?echo $row[0]?>:</span>
                                        <span class="value blue"><?echo $row[1]?></span>
                                    </div>
                                    <? echo get_button("save-button background-lightblue", "החלפת מגמה")?>
                                </div>
                                <div class="clause flex-display flex-part-30">
                                    <div class="label-value flex-part-70">
                                        <span class="label lightblue bold">שותף:</span>
                                        <span class="value blue"><?echo "אהרון לוי"/*$student->partner1*/?></span>
                                    </div>
                                    <? echo get_button("save-button background-lightblue", "החלפת שותף")?>
    
                                </div>
                            </div>
                        <?}?>
                    </div>
                    <div class="tabcontent box" style="display: none" data-tab="2">
                        
                    </div>
                    <div class="tabcontent box" style="display: none" data-tab="3">


                    </div>
                </div>
            </div>
            <div class="buttons-area box flex-display padding-sides-25 border-darkblue">
                 <? echo get_button("save-button background-darkblue center", "החלפת שותף")?>
                  <? echo get_button("save-button border-darkblue darkblue center", "החלפת שותף")?>
            </div>
        </div>   
    <script type="text/javascript">
        function openTab(evt) {
            jQuery('.tablinks').removeClass("active");
            jQuery('.tabcontent').removeClass("active");
            jQuery('.tabcontent').css("display", "none");
            var tabName = jQuery(evt.currentTarget).attr("data-tab");
            jQuery(evt.currentTarget).addClass("active");
            jQuery(".tabcontent[data-tab="+tabName+"]").css("display", "block");
        }
	</script>
  </section>
</main>
 
<?php get_sidebar( 'content-bottom' ); ?>

<?php get_sidebar(); ?>
<?php //get_footer(); ?>