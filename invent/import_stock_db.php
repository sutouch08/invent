<?php 
	$page_name = "นำเข้ายอดคงเหลือต้นงวด";
	$id_profile = $_COOKIE['profile_id'];
?>
<div class="container">

<div class="row" style="height:35px;">
	<div class="col-lg-8" style="padding-top:10px;"><h4 class="title"><i class="fa fa-file-text-o"></i> <?php echo $page_name; ?></h4></div>
    <div class="col-lg-4">
   		<p class="pull-right" style="margin-bottom:0px;">
        	<button class="btn btn-info btn-sm" type="button" onclick="do_export_all()"><i class="fa fa-file-text-o"></i> ส่งออกรายการทั้งหมด</button>
        	<button class="btn btn-success btn-sm" type="button" onclick="do_export()"><i class="fa fa-file-text-o"></i> ส่งออกรายการที่เลือก</button>     
        </p>
    </div>
</div>
<hr style='border-color:#CCC; margin-top: 0px; margin-bottom:10px;' />

<div class="row">

</div>

</div><!-- container -->
