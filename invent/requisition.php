<?php 
	$page_menu = "invent_product_out";
	$page_name = $pageTitle;
	$id_tab = 7;
	$id_profile = $_COOKIE['profile_id'];
    $pm = checkAccess($id_profile, $id_tab);
	$view = $pm['view'];
	$add = $pm['add'];
	$edit = $pm['edit'];
	$delete = $pm['delete'];
	accessDeny($view);
  	if($add==1){ $can_add = "";}else{ $can_add = "style='display:none;'"; }
	if($edit==1){ $can_edit = "";}else{ $can_edit = "style='display:none;'"; }
	if($delete==1){ $can_delete = "";}else{ $can_delete ="style='display:none;'"; }	
	?>
<div class="container">
<!-- page place holder -->
<div class="row">
	<div class="col-xs-6"><h3><span class="glyphicon glyphicon-shopping-cart"></span>&nbsp;<?php echo $page_name; ?></h3>
	</div>
    <div class="col-xs-6">
       <ul class="nav navbar-nav navbar-right">
       <?php 
	   if(isset($_GET['edit'])&&isset($_GET['id_order'])){
		   $id_order = $_GET['id_order'];
		   $order= new order($id_order);
		   if($order->valid==1 || $order->current_state !=1 && $order->current_state !=3){ $active = "style='display:none;'";}else{$active = ""; }
		    echo"
		   <li><a href='index.php?content=requisition' style='text-align:center; background-color:transparent;'><button type='button' class='btn btn-link'><span class='glyphicon glyphicon-arrow-left' style='color:#5cb85c; font-size:30px;'></span><br />กลับ</button></a></li>
       		<li $can_edit ><a href='index.php?content=requisition&add=y&id_order=$id_order' style='text-align:center; background-color:transparent;' $active ><button type='button' class='btn btn-link'  $active><span class='glyphicon glyphicon-pencil' style='color:#5cb85c; font-size:30px;'></span><br />แก้ไข</button></a></li>";
			}else if(isset($_GET['edit']) || isset($_GET['add'])){
		   echo"
		   <li><a href='index.php?content=requisition' style='text-align:center; background-color:transparent;'><button type='button' class='btn btn-link'><span class='glyphicon glyphicon-arrow-left' style='color:#5cb85c; font-size:30px;'></span><br />กลับ</button></a></li>";
		     if(isset($_GET['id_order'])){$id_order = $_GET['id_order'];
		  echo"	<li><a href='controller/requisitionController.php?save_order&id_order=$id_order' style='text-align:center; background-color:transparent;'><button type='button' class='btn btn-link' onclick='edit_stock()'><span class='glyphicon glyphicon-floppy-disk' style='color:#5cb85c; font-size:30px;'></span><br />บันทึก</button></a></li>";}
	  		}else{
		   echo"
		   <li $can_add><a href='controller/requisitionController.php?check_add' style='text-align:center; background-color:transparent; padding-bottom:0px;' ><button type='button' class='btn btn-link' ><span class='glyphicon glyphicon-plus-sign' style='color:#5cb85c; font-size:30px;'></span><br />$page_name</button></a></li>";
	   }
	   ?>
       </ul>
    </div>
</div>
<hr style='border-color:#CCC; margin-top: 0px; margin-bottom:15px;' />
<!-- End page place holder -->
<?php 
if(isset($_GET['error'])){
	$error_message = $_GET['error'];
	echo"<div id='error' class='alert alert-danger' >
	<b>มีบางอย่างผิดพลาด&nbsp;</b>$error_message</div>";
} 
if(isset($_GET['message'])){
	$message = $_GET['message'];
	echo"<div class='alert alert-success'>$message</div>";
}
if(isset($_GET['missing'])){
	$missing = $_GET['missing'];
	echo"<div id='error' class='alert alert-danger'><b>มีบางอย่างผิดพลาด&nbsp;</b>$missing</div>";
}
if(isset($_GET['message1'])){
	$message1 = $_GET['message1'];
echo "<div class='alert alert-warning' role='alert'>$message1</div>";
}
//----------------------------------------------เพิ่ม-----------------------------------------//
if(isset($_GET['add'])){ 
$user_id = $_COOKIE['user_id'];

if(isset($_GET['id_order'])){ 
$id_order = $_GET['id_order'];
$active = "disabled='disabled'"; 
$order = new order($id_order);
$employee = new employee($order->id_employee);
$id_employee = $employee->id_employee;
$employee_name = $employee->full_name; 
$comment = $order->comment;
$payment = $order->payment;
$role = $order->role;
$new_ref = $order->reference;
$customer = new customer($order->id_customer);
$id_customer = $customer->id_customer;
$customer_name = $customer->full_name; 
}else{ 
	$active = "";
	$id_employee = "";
	$employee_name = "";
	$comment = "";
	$payment = "credit";
	$role = "";
	$id_customer = "";
	$customer_name = ""; 
	$new_ref = get_max_role_reference("PREFIX_REQUISITION",2);
}
echo"<form id='add_order_form' action='controller/requisitionController.php?add=y' method='post'>
	<div class='row'><input type='hidden' name='id_employee' value='$user_id' />
	<div class='col-xs-3'><div class='input-group'><span class='input-group-addon'>เลขที่เอกสาร</span><input type='text' id='doc_id' class='form-control' value='$new_ref' disabled='disabled'/></div> </div> 
	<div class='col-xs-2'><div class='input-group'><span class='input-group-addon'>วันที่</span><input type='text' id='doc_date' name='doc_date' class='form-control' value='".date('d-m-Y')."' $active/></div> </div>
	<div class='col-xs-4'><div class='input-group'><span class='input-group-addon'>ผู้เบิก</span><input type='text' id='employee_name' class='form-control' value='$employee_name' autocomplete='off' $active/></div> </div>
	<div class='col-xs-3'><div class='input-group'><span class='input-group-addon'>วัตถุประสงค์</span>
							<select name='role' id='role' class='form-control' $active>
								<option value='0' >เลือกวัตถุประสงค์</option>
								<option value='2'"; if($role==2){ echo"selected='selected'";} echo">เบิกแปรรูปเพื่อขาย</option>
								<option value='6'"; if($role==6){ echo"selected='selected'";} echo">เบิกแปรรูปเพื่อสปอนเซอร์</option>
							</select>
							</div> </div>
	</div>
	<div class='row' style='margin-top:15px;'><input type='hidden' name='employee' id='id_employee' value='$id_employee' />
	<div class='col-xs-3'><div class='input-group'><span class='input-group-addon'>อ้างถึงลูกค้า</span><input type='text' id='customer_name' class='form-control' value='$customer_name' autocomplete='off' $active/></div> <input type='hidden' name='id_customer' id='id_customer' value='$id_customer' /></div> 
	<div class='col-xs-6'><div class='input-group'><span class='input-group-addon'>หมายเหตุ</span><input type='text' id='comment' name='comment' class='form-control' value='$comment' autocomplete='off' $active/></div></div>
	<div class='col-xs-2'><button class='btn btn-default' type='button' id='add_order' $active>&nbsp&nbsp;เพิ่ม&nbsp;&nbsp</button></div>
	</div></form>
	<hr style='border-color:#CCC; margin-top: 15px; margin-bottom:15px;' />";
	if(isset($_GET['id_order'])){ 
		echo"<form id='add_detail_form' action='controller/requisitionController.php?add&insert_detail' method='post'>
			<div class='row'><input type='hidden' name='id_order' id='id_order' value='$id_order' />
			<input type='hidden' name='stock_qty' id='stock_qty' /><input name='id_product_attribute' id='id_product_attribute' type='hidden' />
	<div class='col-xs-3'><div class='input-group'><span class='input-group-addon'>บาร์โค้ด</span><input type='text' id='barcode' class='form-control' autocomplete='off' autofocus /></div> </div> 
	<div class='col-xs-4'><div class='input-group'><span class='input-group-addon'>สินค้า</span><input type='text' id='product_code' class='form-control' autocomplete='off' /></div> </div>
	<div class='col-xs-2'><div class='input-group'><span class='input-group-addon'>ในสต็อก</span><input type='text' id='stock_label' class='form-control' disabled='disabled' /></div> </div>
	<div class='col-xs-2'><div class='input-group'><span class='input-group-addon'>จำนวน</span><input type='text' id='qty' name='qty' class='form-control' autocomplete='off' autofocus /></div> </div>
	<div class='col-xs-1'><button class='btn btn-default' type='button' id='add_detail' onclick='submit_detail()'>&nbsp&nbsp;เพิ่ม&nbsp;&nbsp</button></div>
	</div></form>
	<hr style='border-color:#CCC; margin-top: 15px; margin-bottom:15px;' />";
	//เริ่ม order 
	echo"
	<div class='row'>
	<div class='col-lg-12 col-md-12 col-sm-12 col-sx-12'>
	<ul class='nav nav-tabs' role='tablist' style='background-color:#EEE'>";
	$sql = dbQuery("SELECT id_category, category_name FROM tbl_category WHERE parent_id = 0 AND level_depth = 1 ORDER BY position ASC");
				$row = dbNumRows($sql);
				$i=0;
				while($i<$row){
				list($id_category, $category_name) = dbFetchArray($sql);
				$sqr = dbQuery("SELECT id_category, category_name FROM tbl_category WHERE parent_id = $id_category ORDER BY position ASC");
				$rs = dbNumRows($sqr);
				$n=0;
				if($rs<1){
					echo"<li calss=''><a href='#cat-$id_category' role='tab' data-toggle='tab'>$category_name</a>";
				}else{				
				echo"<li class='dropdown'><a id='ul-$id_category' class='dropdown-toggle' data-toggle='dropdown' href='#'>$category_name<span class='caret'></span></a>";
				echo"<ul class='dropdown-menu' role='menu' aria-labelledby='ul-$id_category'>";
				echo"<li class=''><a href='#cat-$id_category' tabindex='-1' role='tab' data-toggle='tab'>$category_name</a></li>";     
				while($n<$rs){
				list($id_sub_category, $sub_category_name) = dbFetchArray($sqr);
				echo" <li class=''><a href='#cat-$id_sub_category' tabindex='-1' role='tab' data-toggle='tab'>$sub_category_name</a></li>";
				$n++;
				}
				echo"</ul></li>";
				}	
				echo "</li>";
				$i++;
				}
	echo"
	</ul>
	</div>
	</div>
<div class='row'><div class='col-lg-12 col-md-12 col-sm-12 col-sx-12'>
<hr style='border-color:#CCC; margin-top: 0px; margin-bottom:15px;' />	
<div class='tab-content'>";	
$query = dbQuery("SELECT id_category, category_name FROM tbl_category WHERE id_category !=0");
	$rc = dbNumRows($query);
	$r =0;
	while($c = dbFetchArray($query)){
		$id_category = $c['id_category'];
		$cate_name = $c['category_name'];
		echo"<div class='tab-pane"; if($r==0){ echo" active";} echo"' id='cat-$id_category'>";	
		$sql = dbQuery("SELECT tbl_category_product.id_product FROM tbl_category_product LEFT JOIN tbl_product ON tbl_category_product.id_product = tbl_product.id_product WHERE id_category = $id_category ORDER BY product_code ASC");
		$row = dbNumRows($sql); 
		if($row>0){
			$i=0;
			while($i<$row){
				list($id_product) = dbFetchArray($sql);
				$product = new product();
				$product->product_detail($id_product);
				
		 echo"<div class='col-lg-1 col-md-1 col-sm-3 col-xs-4' style='text-align:center;'>
			<div class='product' style='padding:5px;'>
			<div class='image'><a href='#' onclick='getData(".$product->id_product.")'>".$product->getCoverImage($product->id_product,1,"img-responsive")."</a></div>
			<div class='description' style='font-size:10px; min-height:50px;'><a href='#'  onclick='getData(".$product->id_product.")'>".$product->product_code."</a></div>
			  </div></div>";
				$i++;
				$r++;
			}
		}else{ 
			echo"<br/><h4 style='text-align:center;'>ยังไม่มีรายการสินค้า</h4>";
		}
		echo "</div>";
	}	
	echo"</div> <button data-toggle='modal' data-target='#order_grid' id='btn_toggle' style='display:none;'>toggle</button>
</div></div>";	
//0จบ
echo"			
	<form action='controller/requisitionController.php?add_to_order' method='post'>
	<div class='modal fade' id='order_grid' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'>
								  <div class='modal-dialog' id='modal'>
									<div class='modal-content'>
									  <div class='modal-header'>
										<button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>
										<h4 class='modal-title' id='modal_title'>title</h4><input type='hidden' name='id_order' value='$id_order'/>
									  </div>
									  <div class='modal-body' id='modal_body'></div>
									  <div class='modal-footer'>
										<button type='button' class='btn btn-default' data-dismiss='modal'>ปิด</button>
										<button type='submit' class='btn btn-primary'>เพิ่มในรายการ</button>
									  </div>
									</div>
								  </div>
								</div></form>";
	echo"<hr style='border-color:#CCC; margin-top: 15px; margin-bottom:15px;' />
	<div class='row'>
	<table class='table' id='order_detail'>
	<thead>
				<th stype='width:5%; text-align:center;'>ลำดับ</th><th style='text-align:center;'>รูป</th><th style='width:15%;'>บาร์โค้ด</th><th style='width:30%;'>สินค้า</th>
			   <th style='width:10%; text-align:center;'>ราคา</th><th style='width:10%; text-align:center;'>จำนวน</th>
			   <th style='width:10%; text-align:center;'>ส่วนลด</th><th style='width:10%; text-align:center;'>มูลค่า</th><th style='text-align:center;'>การกระทำ</th>
	</thead>";
	$sql = dbQuery("SELECT id_order_detail, id_product_attribute, barcode, product_reference, product_name, product_price, product_qty, reduction_percent, reduction_amount, discount_amount, total_amount FROM tbl_order_detail WHERE id_order = $id_order");
	$row = dbNumRows($sql);
	$n = 1;
	$i = 0;
	if($row>0){
	while($i<$row){
		list($id_order_detail, $id_product_attribute, $barcode, $product_reference, $product_name, $product_price, $product_qty, $discount_percent, $discount_amount, $total_discount, $total_amount)= dbFetchArray($sql);
		$product = new product();
		$product->product_attribute_detail($id_product_attribute);
		if($discount_percent !== 0.00){ $discount = $discount_percent ."%";}else if($discount_amount != 0.00){ $discount = $discount_amount . "฿" ;}
		echo"<tr><td style='text-align:center; vertical-align:middle;'>$n</td>
		<td style='text-align:center; vertical-align:middle;'><img src='".$product->get_product_attribute_image($id_product_attribute,1)."' /> </td>
		<td style='vertical-align:middle;'>$barcode</td>
		<td style='vertical-align:middle;'>$product_reference : $product_name</td>
		<td style='text-align:center; vertical-align:middle;'>".number_format($product_price,2)."</td>
		<td style='text-align:center; vertical-align:middle;'>".number_format($product_qty)."</td>
		<td style='text-align:center; vertical-align:middle;'>$discount</td>
		<td style='text-align:center; vertical-align:middle;'>".number_format($total_amount,2)."</td>
		<td style='text-align:center; vertical-align:middle;'><a href='controller/requisitionController.php?delete=y&id_order_detail=$id_order_detail' >
				<button type='button' class='btn btn-danger btn-sx' onclick=\"return confirm('คุณแน่ใจว่าต้องการลบ $product_reference : $product_name'); \" >
				<span class='glyphicon glyphicon-trash' style='color: #fff;'></span></button></a>
				</td></tr>";
				$i++;
				$n++;
	}
	}else{
		echo"<tr><td colspan='8' align='center'><h3>ยังไมีมีรายการสินค้า</h3></td></tr>";
	}
	echo"
				
	</table>	
	";
	
	 }
}else if(isset($_GET['edit'])&&isset($_GET['id_order'])){
//*********************************************** แก้ไขออเดอร์ **************************************************************//
	//echo"<form id='state_change' action='controller/orderController.php?edit&state_change' method='post'>";
	$id_employee = $_COOKIE['user_id'];
	$id_order = $_GET['id_order'];
	$order = new order($id_order);
	$employee = new employee($order->id_employee);
	$state = $order->orderState();
	if($order->id_customer != "0"){
	$customer = new customer($order->id_customer);
	}
	echo"		
        <div class='row'>
        	<div class='col-xs-12'><h4>".$order->reference." : ". $order->role_name." <p class='pull-right'>ผู้เบิก : &nbsp;".$employee->full_name."</p></h4></div>
        </div>
		<hr style='border-color:#CCC; margin-top: 0px; margin-bottom:15px;' />
		<div class='row'>
		<div class='col-xs-12'>
		<dl style='float:left; margin-left:10px;'><dt style='float:left; margin:0px; padding-right:10px'>วันที่เบิก : &nbsp;</dt><dd style='float:left; margin:0px; padding-right:10px'>".date("d-m-Y H:i:s", strtotime($order->date_add))."</dd>  |</dt></dl>
		<dl style='float:left; margin-left:10px;'><dt style='float:left; margin:0px; padding-right:10px'>สินค้า :&nbsp;</dt><dd style='float:left; margin:0px; padding-right:10px'>".number_format($order->total_product)."</dd>  |</dt></dl>
		<dl style='float:left; margin-left:10px;'><dt style='float:left; margin:0px; padding-right:10px'>จำนวน : &nbsp;</dt><dd style='float:left; margin:0px; padding-right:10px'>".number_format($order->total_qty)."</dd>  |</dt></dl>
		<dl style='float:left; margin-left:10px;'><dt style='float:left; margin:0px; padding-right:10px'>ยอดเงิน : &nbsp;</dt><dd style='float:left; margin:0px; padding-right:10px'>".number_format($order->total_amount,2)."&nbsp;฿</dd> </dt></dl><p class='pull-right'><a href='controller/orderController.php?print_order&id_order=$id_order' ><span class='glyphicon glyphicon-print' style='color:#5cb85c; font-size:30px;'></span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
		</div></div>
		<hr style='border-color:#CCC; margin-top: 0px; margin-bottom:15px;' />
		<div class='row'><form id='state_change' action='controller/requisitionController.php?edit&state_change' method='post'>
		<div class='col-xs-6'>
		<table class='table' style='width:100%; padding:10px; border: 1px solid #ccc;'><tr><input type='hidden' name='id_order' value='".$order->id_order."' /><input type='hidden' name='id_employee' value='$id_employee' />
		<td style='width:25%; text-align:right; vertical-align:middle;'>สถานะ :&nbsp; </td><td style='width:40%; padding-right:10px;'>
		<select name='order_state' id='order_state' class='form-control input-sm'>
			<option value='0'>---- สถานะ ----</option>
				<option value='1'>รอการชำระเงิน</option>
				<option value='3'>รอจัดสินค้า</option>";
			if( $delete == 1 ){ echo "<option value='8'>ยกเลิก</option>"; }
		echo"</select></td><td style='padding-right:10px;'><button class='btn btn-default' type='button' onclick='state_change()'>เพิ่ม</button></td></tr>";
		$row = dbNumRows($state);
		$i=0;
		if($row>0){
			while($i<$row){
				list($id_order_state, $state_name, $first_name, $last_name, $date_add)=dbFetchArray($state);
			echo"
			<tr  style='background-color:".state_color($id_order_state).";'><td style='padding-top:10px; padding-bottom:10px; text-align:center;'>$state_name</td>
			<td style='padding-top:10px; padding-bottom:10px; text-align:center;'>$first_name  $last_name</td>
			<td style='padding-top:10px; padding-bottom:10px; text-align:center;'>".date('d-m-Y H:i:s', strtotime($date_add))."</td></tr>";
			$i++;
			}
		}else{
		echo"<tr><td style='padding-top:10px; padding-bottom:10px; text-align:center;'>".$order->currentState()."</td>
		<td style='padding-top:10px; padding-bottom:10px; text-align:right;'></td>
		<td style='padding-top:10px; padding-bottom:10px; text-align:center;'>".date('d-m-Y H:i:s', strtotime($order->date_upd))."</td></tr>";
		}
		echo"
		</table></div></form><div class='col-xs-6'>";
	
		echo "</div><!--col --></div><!--row-->
		<hr style='border-color:#CCC; margin-top: 0px; margin-bottom:15px;' />	
		<form id='edit_order_form' action='controller/requisitionController.php?edit_order&add_detail' method='post' autocomplete='off'>
		<div class='row'>"; echo $order->orderProductTable(); echo "</div><div class='row'><p><h4>อ้างถึงลูกค้า :  ";if($order->id_customer != "0"){ echo $customer->full_name;} echo "</h4></p></div><div class='row'><p><h4>ข้อความ :  "; echo $order->comment; echo "</h4></p></div><h4></h4></form>";
//*********************************************** จบหน้าแก้ไข ****************************************************//
}else{
//************************************************ แสดงรายการ *************************************************//
$paginator = new paginator();
echo"<form  method='post' id='form'>
		<div class='row'>
			<div class='col-xs-2 col-xs-offset-4'>
				<div class='input-group'>
					<span class='input-group-addon'> จาก :</span>
					<input type='text' class='form-control' name='from_date' id='from_date'  value='";
					 if(isset($_POST['from_date']) && $_POST['to_date'] && $_POST['from_date'] && $_POST['to_date'] !="เลือกวัน"){ echo date('d-m-Y',strtotime($_POST['from_date']));} else { echo "เลือกวัน";} 
					 echo "'/>
				</div>		
			</div>	
			<div class='col-xs-2 '>
				<div class='input-group'>
					<span class='input-group-addon'>ถึง :</span>
				 <input type='test' class='form-control'  name='to_date' id='to_date' value='";
				  if(isset($_POST['from_date']) && $_POST['to_date'] && $_POST['from_date'] && $_POST['to_date'] !="เลือกวัน"){ echo date('d-m-Y',strtotime($_POST['to_date']));} else{ echo "เลือกวัน";}  echo"' />
				</div>
			</div>
			<div class='col-xs-1'>
					<button type='button' class='btn btn-default' onclick='validate()'>แสดง</button>
			</div>	
         </div>
				</form>
				<hr style='border-color:#CCC; margin-top: 15px; margin-bottom:0px;' />";
				$view = "";
		if(isset($_POST['from_date'])){	$from = date('Y-m-d',strtotime($_POST['from_date'])); }else{ $from = "";} if(isset($_POST['to_date'])){  $to =date('Y-m-d',strtotime($_POST['to_date'])); }else{ $to = "";}
		if($from==""){
			if($to==""){
				$view = "week";
			}
		}if($view !=""){
			$date = getLastDays($view);
			$from = $date['from'];
			$to = $date['to'];
		}
		if(isset($_POST['get_rows'])){$get_rows = $_POST['get_rows'];$paginator->setcookie_rows($get_rows);}else if(isset($_COOKIE['get_rows'])){$get_rows = $_COOKIE['get_rows'];}else{$get_rows = 50;}
		$paginator->Per_Page("tbl_order","WHERE (tbl_order.date_add BETWEEN '$from' AND '$to') AND current_state !=1 AND role IN(2,6) ORDER BY id_order",$get_rows);
		$paginator->display($get_rows,"index.php?content=requisition");
		echo "
<div class='row'>
<div class='col-xs-12'>
	<table class='table'>
    	<thead style='color:#FFF; background-color:#48CFAD;'>
        	<th style='width:5%; text-align:center;'>ID</th><th style='width:10%;'>เลขที่อ้างอิง</th>
            <th style='width:10%;'>พนักงาน</th><th style='width:10%;'>ลูกค้า</th><th style='width:10%; text-align:center;'>ยอดเงิน</th>
			<th style='width:10%; text-align:center;'>สถานะ</th>
			<th style='width:10%; text-align:center;'>วันที่เพิ่ม</th><th style='width:10%; text-align:center;'>วันที่ปรับปรุง</th>
        </thead>";
		if($view !=""){
			$date = getLastDays($view);
			$from = $date['from'];
			$to = $date['to'];
		}
		$result = dbQuery("SELECT id_order,reference,id_customer,id_employee,payment,tbl_order.date_add,current_state,tbl_order.date_upd,order_status FROM tbl_order WHERE (tbl_order.date_add BETWEEN '$from' AND '$to') AND role IN(2,6) ORDER BY id_order DESC LIMIT ".$paginator->Page_Start." , ".$paginator->Per_Page);
		$i=0;
		$row = dbNumRows($result);
		if($row>0){
		while($i<$row){
			list($id_order, $reference,$id_customer,$id_employee,  $payment,   $date_add,$current_state,$date_upd,$order_status)=dbFetchArray($result);
			if($id_customer == ""){$cus_first_name = "";$cus_last_name = "";}else{list($cus_first_name, $cus_last_name) = dbFetchArray(dbQuery("SELECT first_name,last_name FROM tbl_customer WHERE id_customer = '$id_customer'"));}
			list($employee_name) = dbFetchArray(dbQuery("SELECT first_name FROM tbl_employee WHERE id_employee = '$id_employee'"));
			list($amount) = dbFetchArray(dbQuery("SELECT SUM(total_amount) FROM tbl_order_detail WHERE id_order = $id_order"));
			list($status) = dbFetchArray(dbQuery("SELECT state_name FROM tbl_order_state WHERE id_order_state = '$current_state'"));
			if($order_status == 0 ){
				$location = "content=requisition&add=y&id_order=$id_order";
			}else{
				$location = "content=requisition&edit=y&id_order=$id_order&view_detail=y";
			}
	echo "<tr ";if($order_status == 0 ){}else{echo "style='color:#FFF;background-color:".state_color($current_state).";'";}echo ">
				<td align='center' style='cursor:pointer;' onclick=\"document.location='index.php?$location'\">$id_order</td>
				<td style='cursor:pointer;' onclick=\"document.location='index.php?$location'\">$reference</td>
				<td style='cursor:pointer;' onclick=\"document.location='index.php?$location'\">$employee_name</td>
				<td style='cursor:pointer;' onclick=\"document.location='index.php?$location'\">$cus_first_name $cus_last_name</td>
				<td align='center' style='cursor:pointer;' onclick=\"document.location='index.php?$location'\">"; echo number_format($amount)."</td>
				<td align='center' style='cursor:pointer;' onclick=\"document.location='index.php?$location'\">";if($order_status == 0 ){echo "ยังไม่ได้บันทึก";}else{echo $status;}echo "</td>
				<td align='center' style='cursor:pointer;' onclick=\"document.location='index.php?$location'\">"; echo thaiDate($date_add)."</td>
				<td align='center' style='cursor:pointer;' onclick=\"document.location='index.php?$location'\">"; echo thaiDate($date_upd)."</td>
			</tr>";
			$i++;
		}
		}else if($row==0){
			echo"<tr><td colspan='9' align='center'><h3><span class='glyphicon glyphicon-exclamation-sign'></span>&nbsp;ไม่มีรายการในช่วงนี้</h3></td></tr>";
		}
		echo"</table>";
		echo $paginator->display_pages();
		echo "<br><br>";
}
?>
<script language="javascript"> 
$(function() {
    $("#from_date").datepicker({
      dateFormat: 'dd-mm-yy', onClose: function( selectedDate ) {
        $( "#to_date" ).datepicker( "option", "minDate", selectedDate );
      }
    });
    $( "#to_date" ).datepicker({
      dateFormat: 'dd-mm-yy',   onClose: function( selectedDate ) {
        $( "#from_date" ).datepicker( "option", "maxDate", selectedDate );
      }
    });
  });
  $(function() {
    $("#date").datepicker({
      dateFormat: 'dd-mm-yy'
    });
  });
   function validate() {
	var from_date = $("#from_date").val();
	var to_date = $("#to_date").val();
	if(from_date =="เลือกวัน"){	
		alert("คุณยังไม่ได้เลือกช่วงเวลา");
		}else if(to_date ==""){
		alert("คุณยังไม่ได้เลือกวันสุดท้าย");	
	}else{
		$("#form").submit();
	}
}	
	function state_change(){
		var state = $("#order_state").val();
		if(state == 0){
			alert("ยังไม่ได้เลือกสถานะ");
		}else{
			$("#state_change").submit();
		}
	}
$("#add_product").click(function() {
    $("#new_row").css("display","");
	var id_cus = $("#id_customer").val();
	$("#id_cus").val(id_cus);
});
$(document).ready(function(e) {
	$("#product").autocomplete(
	{
		 source: "controller/requisitionController.php?product",
		 close: function(event,ui){
			 var ref = $(this).val();
			var id_cus = $("#id_cus").val();	
		$.ajax({ 
			 url: "controller/requisitionController.php?reference="+ref+"&id_customer="+id_cus,
			 type: "GET", cache:false, 
			 success: function(data){
				 if(data !=""){ 
				 	var arr = data.split(':');
					var id = arr[0];
					var price = arr[1];
					var stock = arr[2];
					 $("#id_product_attribute").val(id);
					 $("#price").val(price);
					 $("#available").text("คงเหลือ : "+stock);
					 $("#stock_qty").val(stock);
				 }
			 }
		});
    }
	});

});
$(document).ready(function(e) {
    $("#product_code").autocomplete({
		source:"controller/requisitionController.php?product_code",
		autoFocus: true,
		close: function(event,ui){
			var data = $("#product_code").val();
		}
	});			
});
//// product code
$("#product_code").focusout(function(e) {
    var product_code = $(this).val();
	if(product_code !=""){
	$.ajax({
		url:"controller/requisitionController.php?check_stock&product_code="+product_code
		,type:"GET",cache:false,success: function(stock_qty){
			if(stock_qty !=""){
				var arr = stock_qty.split(":");
				$.trim($("#id_product_attribute").val(arr[0]));
				$("#stock_qty").val(arr[1]);
				$("#stock_label").val(arr[1]);
			}
		}
	});
	}
});
//// barcode 
$("#barcode").focusout(function(e) {
    var barcode = $(this).val();
	if(barcode !=""){
	$.ajax({
		url:"controller/requisitionController.php?check_stock&barcode="+barcode
		,type:"GET",cache:false,success: function(data){
			if(data !=""){
				var arr = data.split(":");
				$("#id_product_attribute").val(arr[0]);
				$("#stock_qty").val(arr[1]);
				$("#stock_label").val(arr[1]);
				$("#product_code").val(arr[2]);
			}
		}
	});
	}
});
/////
$("#qty").keyup(function(e) {
    var limit = parseInt($("#stock_qty").val());
	var qty = parseInt($("#qty").val());
	if(qty>limit){
		alert("มีสินค้าในสต็อกแค่ "+limit+" ตัวเท่านั้น");
		$("#qty").val(limit);
	}
});
$("#product_code").bind("enterKey",function(){
	if($("#product_code").val() != ""){
	$("#qty").focus();
	}
});
///// ชื่อสินค้า เมื่อกดปุ่ม enter
	$("#product_code").bind("enterKey",function(){
	if($("#product_code").val() != ""){
	$("#qty").focus();
	}
});
$("#product_code").keyup(function(e){
    if(e.keyCode == 13)
    {
        $(this).trigger("enterKey");
    }
});
/// barcode 
$("#barcode").bind("enterKey",function(){
	if($("#barcode").val() != ""){
		$("#qty").focus();
	}else{
		$("#product_code").focus();
	}
});
$("#barcode").keyup(function(e){
    if(e.keyCode == 13)
    {
        $(this).trigger("enterKey");
    }
});
$("#qty").bind("enterKey",function(){
	if($("#qty").val() != ""){
		$("#add_detail").click();
	}
});
$("#qty").keyup(function(e){
    if(e.keyCode == 13)
    {
        $(this).trigger("enterKey");
    }
});
$(document).ready(function(e) {
    $("#qty").keyup(function(e) {
        var qty = parseInt($(this).val());
		var stock = parseInt($("#stock_qty").val());
		var price = $("#price").val();
		if(qty>stock){
			alert("มีสินค้าในสต็อกแค่ "+stock);
			$(this).val(stock);
			var total = price * qty;
			$("#total_amount").val(total);
			$("#total").html(total +" ฿");
		}else{
			var total = price * qty;
		$("#total_amount").val(total);
		$("#total").html(total +" ฿");
		}
    });
});
function edit_product(id_order, id_product_attribute){
	var name = id_order.toString() + id_product_attribute.toString();
	var qty = parseInt($("#qty"+name).text());
	$("#edit"+name).css("display","none");
	$("#delete"+name).css("display","none");
	$("#update"+name).css("display","");
	$("#edit_qty"+name).val(qty);
	$("#qty"+name).css("display","none");
	$("#edit_qty"+name).css("display","");	
}
function update(id_order, id_product_attribute){
	var name = id_order.toString() + id_product_attribute.toString();
	var qty = $("#edit_qty"+name).val();
	var old_qty = parseInt($("#qty"+name).text());
	if(qty<=0){
		alert("จำนวนที่สั่งอย่างน้อย 1 ตัว");
	}else{
	$.ajax({
		url:"controller/requisitionController.php?check_stock&id_order="+id_order+"&id_product_attribute="+id_product_attribute, 
		cache:false,
		success: function(stock_qty){
			var stock = parseInt(stock_qty);
			if(qty<=old_qty){
				$("#new_qty").val(qty);
				$("#id_order").val(id_order);
				$("#id_product_attribute").val(id_product_attribute);
				$("#edit_order_form").submit();
			}else if(qty>stock){
				alert("มีสินค้าในสต็อกแค่ "+stock+" เท่านั้น");
				$("#edit_qty"+name).val(stock);
				//$("#new_qty").val(stock);
			}else{
				$("#new_qty").val(qty);
				$("#id_order").val(id_order);
				$("#id_product_attribute").val(id_product_attribute);
				$("#edit_order_form").submit();
			}
		}
	});
	}
}
//////// เพิ่มรายการสั่งซื้อสินค้าแต่ไม่เปลียนหน้าใหม่ ///
function submit_detail(){
	var id_order = $("#id_order").val();
	var id_product_attribute =  $("#id_product_attribute").val();
	var order_qty = $("#qty").val();
	var stock_qty = $("#stock_qty").val();
	if(id_order==""){
		alert("ไม่พบตัวแปร id_order ติดต่อผู้ดูแลระบบ");
	}else if(id_product_attribute ==""){
		alert("ไม่พบตัวแปร id_product_attribute ติดต่อผู้ดูแลระบบ");
	}else if(order_qty==""){
		alert("ยังไม่ได้ใส่จำนวนสินค้า");
	}else if(parseInt(order_qty)>parseInt(stock_qty)){
		alert("จำนวนที่สังมากกว่าจำนวนที่มีในสต็อก");
		$("#qty").val(stock_qty);
	}else{
		$("#add_detail_form").submit();
	}
}
//// เพิ่มรายการสั่งซื้อสินค้าแล้วเปลียนหน้าใหม่ ///
function add_detail(){ 
	var id_order = $("#id_order").val();
	var id_product_attribute =  $("#id_product_attribute").val();
	var order_qty = $("#qty").val();
	var stock_qty = $("#stock_qty").val();
	var id_customer = $("#id_customer").val();
	if(id_order==""){
		alert("ไม่พบตัวแปร id_order ติดต่อผู้ดูแลระบบ");
	}else if(id_product_attribute ==""){
		alert("ไม่พบตัวแปร id_product_attribute ติดต่อผู้ดูแลระบบ");
	}else if(id_customer==""){
		alert("ไม่พบตัวแปร id_product_attribute ติดต่อผู้ดูแลระบบ");
	}else if(order_qty==""){
		alert("ยังไม่ได้ใส่จำนวนสินค้า");
	}else if(parseInt(order_qty) > parseInt(stock_qty)){
		alert("จำนวนที่สังมากกว่าจำนวนที่มีในสต็อก");
		$("#qty").val(stock_qty);
	}else{
		$("#edit_order_form").submit();
	}
}
var no = 0;
//////// เพิ่มรายการสั่งซื้อสินค้าแต่ไม่เปลียนหน้าใหม่ ///
function insert_detail(id_order,id_product_attribute, qty){
	$.ajax({
		url:"controller/requisitionController.php?insert_detail",
		data: {id_order:id_order, id_product_attribute:id_product_attribute, qty:qty},type:"POST",cache:false,
		success: function(complete){
			if(complete !="error"){
				no = no +1;
				var data = complete.split(":");
				var barcode = data[0];
				var product = data[1];
				var price = data[2];
				var quantity = data[3];
				var discount = data[4];
				var amount = data[5];
				$("#order_detail").append("<tr><td align='center'>"+no+"</td><td>"+barcode+"</td><td>"+product+"</td><td>"+price+"</td><td>"+quantity+"</td><td>"+discount+"</td><td>"+amount+"</td></tr>");
			}else{ 
			alert("เพิ่มข้อมูลไม่ได้");
			}
		}
	});
}
$(document).ready(function(e) {
    if($("#error").length){
		alert($("#error").text());
	}
});
$(document).ready(function(e) {
    $("#doc_date").datepicker({ 
	dateFormat: 'dd-mm-yy'
	});
});
$(document).ready(function(e) {
    $("#employee_name").autocomplete({
		source:"controller/requisitionController.php?employee_name",
		autoFocus: true,
		close: function(event,ui){
			var data = $("#employee_name").val();
			var arr = data.split(':');
			var id = arr[0];
			$("#id_employee").val(id);
			$(this).val(arr[1]);
		}
	});			
});
$("#add_order").click(function(e) {
    var date = $("#doc_date").val();
	var role = $("#role").val();
	var emp = $("#id_employee").val();
	if(date ==""){
		alert("ยังไม่ได้ระบุวันที่");
	}else if(emp ==""){
		alert("ยังไม่ได้ระบุผู้เบิก");
	}else if(role ==0){
		alert("ยังไม่ได้ระบุวัตถุประสงค์");
	}else{
		$("#add_order_form").submit();
	}
});
function get_row(){
	$("#rows").submit();
}
function getData(id_product){
	var id_cus = 0;
	$.ajax({
		url:"controller/orderController.php?getData&id_product="+id_product+"&id_customer="+id_cus,
		type:"GET", cache:false, 
		success: function(dataset){
			if(dataset !=""){
				var arr = dataset.split("|");
				var data = arr[0];
				var table_w = arr[1];
				var title = arr[2];
				$("#modal").css("width",table_w+"px");
				$("#modal_title").html(title);
				$("#modal_body").html(data);
				$("#btn_toggle").click();
			}else{
				alert("NO DATA");
			}		
		}
	});
}
$(document).ready(function(e) {
    $("#customer_name").autocomplete({
		source:"controller/orderController.php?customer_name",
		autoFocus: true,
		close: function(event,ui){
			var data = $("#customer_name").val();
			var arr = data.split(' | ');
			var id = arr[0];
			var name = arr[1];
			var id_customer = arr[2];
			$("#id_customer").val(id_customer);
			$(this).val(name);
		}
	});			
});
</script>