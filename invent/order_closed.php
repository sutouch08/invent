<?php 
	$page_menu = "invent_order_closed";
	$page_name = "รายการเปิดบิลแล้ว";
	$id_tab = 20;
	$id_profile = $_COOKIE['profile_id'];
    $pm = checkAccess($id_profile, $id_tab);
	$view = $pm['view'];
	$add = $pm['add'];
	$edit = $pm['edit'];
	$delete = $pm['delete'];
	accessDeny($view);
	include "function/support_helper.php";
	require 'function/product_helper.php';
	require 'function/qc_helper.php';
	function get_temp_qty($id_order, $id_product_attribute)
	{
		$qty = 0;
		$qs = dbQuery("SELECT SUM(qty) AS qty FROM tbl_temp WHERE id_order = ".$id_order." AND id_product_attribute = ".$id_product_attribute);
		if(dbNumRows($qs) == 1 )
		{
			$rs = dbFetchArray($qs);
			$qty = $rs['qty'];
		}
		return $qty;
	}
	 function show_discount($percent, $amount)
		 {
			 $unit 	= " %";
			 $dis	= 0.00;
			if($percent != 0.00){ $dis = $percent; }else{ $dis = number_format($amount, 2); $unit = ""; }
			return $dis.$unit;
		 }
	
	function get_sold_data($id_order, $id_product_attribute)
	{
		$rs = false;
		$qs = dbQuery("SELECT * FROM tbl_order_detail_sold WHERE id_order = ".$id_order." AND id_product_attribute = ".$id_product_attribute);
		if( dbNumRows($qs) == 1 )
		{
			$rs = dbFetchArray($qs);	
		}
		return $rs;
	}
	?>   
<div class="container">
<!-- page place holder -->
<div class="row">
	<div class="col-sm-6"><h3 class="title"><i class="fa fa-file-text-o"></i>&nbsp;<?php echo $page_name; ?></h3>
  </div>
    <div class="col-sm-6">
       <p class="pull-right">
       <?php if(isset($_GET['view_detail'])&&isset($_GET['id_order'])) : ?>
		    <a href='index.php?content=order_closed' style="text-decoration:none:"><button type='button' class='btn btn-warning'><i class="fa fa-arrow-left" style="margin-right:5px;"></i>กลับ</button></a>   		   
	   <?php endif; ?>
       </p>
    </div>
</div>
<hr style='border-color:#CCC; margin-top: 0px; margin-bottom:15px;' />
<!-- End page place holder -->
<!------------------------------------------ แสดงรายละเอียด ------------------------------------>
<?php if(isset($_GET['print_packing_list']) && isset( $_GET['id_order']) ) : ?>
	<div class="col-lg-12">
<?php	
	$id_order = $_GET['id_order'];
	$qs = dbQuery("SELECT id_box FROM tbl_box WHERE id_order = ".$id_order);       
	if(dbNumRows($qs) > 0 ) :
		$i = 1; 
		while($ro = dbFetchArray($qs)) :
			$qo = dbQuery("SELECT SUM(qty) AS qty FROM tbl_qc WHERE id_order = ".$id_order." AND id_box = ".$ro['id_box']." AND valid = 1"); 
			$rs = dbFetchArray($qo);  
?>		<a href='controller/qcController.php?print_packing_list&id_order=<?php echo $id_order; ?>&id_box=<?php echo $ro['id_box']; ?>&number=<?php echo $i; ?>' target='_blank'>
			<button type='button' id='print_<?php echo $ro['id_box']; ?>' class="btn btn-success" >
            	<i class="fa fa-print" style="margin-right:5px;"></i>กล่องที่ <?php echo $i; ?> :  <span id='<?php echo $ro['id_box']; ?>'><?php echo $rs['qty']; ?></span> pcs.
             </button>  
 		</a>
<?php   $i++;  
	endwhile;
	else :
			echo "ยังไม่มีการตรวจสินค้าหรือไม่ได้ใช้ระบบกล่อง";
	endif;
	?>   
	</div>	
<?php elseif(isset($_GET['view_detail'])&&isset($_GET['id_order'])) :
	$id_employee = $_COOKIE['user_id'];
	$id_order = $_GET['id_order'];
	$bill_discount = bill_discount($id_order);
	$order = new order($id_order);
	$role = $order->role;
	$customer = new customer($order->id_customer);
	$sale = new sale($order->id_sale);
?>	
<?php if( $order->current_state == 9 || $order->current_state == 10 ) : ?>
<?php if($role == 3) {
				$reference = $order->reference; 
				$cus_label = " ";
				$cus_info = "";
				$em_label = "ผู้ยืม : ";
				$em_info = employee_name($order->id_employee);
			}else if($role == 7) {
				$reference = $order->reference; 
				$cus_label = "  &nbsp;ผู้รับ : ";
				$cus_info = $customer->full_name;
				$em_label = "ผู้เบิก : ";
				$em_info = employee_name($order->id_employee);
				$user = employee_name(get_id_user_support($id_order));
			}else if($role == 2 || $role == 6){
				$reference = $order->reference; 
				$cus_label = "  &nbsp;ลูกค้า : ";
				$cus_info = $customer->full_name;
				$em_label = "ผู้เบิก : ";
				$em_info = employee_name($order->id_employee);
			}else{
				$reference = $order->reference;
				$cus_label = "ลูกค้า : ";
				$cus_info = $customer->full_name;
				$em_label = "พนักงานขาย : ";
				$em_info = $sale->full_name;
			}	
?>	
	  <div class='row'>
        	<div class='col-lg-2'>	<strong><?php echo $reference; ?></strong></div>
            <div class="col-lg-4"><strong><?php echo $cus_label . $cus_info; ?></strong></div>
            <div class="col-lg-6"><strong><p class="pull-right"><?php echo $em_label . $em_info; ?></p></strong> </div>
        </div>
		<hr style='border-color:#CCC; margin-top: 0px; margin-bottom:15px;' />
		<div class='row'>
		<div class='col-lg-12'>
		<dl style='float:left; margin-left:10px;'><dt style='float:left; margin:0px; padding-right:10px'>วันที่สั่ง : &nbsp;</dt><dd style='float:left; margin:0px; padding-right:10px'><?php echo thaiDate($order->date_add); ?></dd>  |</dt></dl>
		<dl style='float:left; margin-left:10px;'><dt style='float:left; margin:0px; padding-right:10px'>สินค้า :&nbsp;</dt><dd style='float:left; margin:0px; padding-right:10px'><?php echo number_format($order->total_product); ?></dd>  |</dt></dl>
		<dl style='float:left; margin-left:10px;'><dt style='float:left; margin:0px; padding-right:10px'>จำนวน : &nbsp;</dt><dd style='float:left; margin:0px; padding-right:10px'><?php echo number_format($order->total_qty); ?></dd>  |</dt></dl>
<?php if($order->role == 7) : ?>
		<dl style='float:left; margin-left:10px;'><dt style='float:left; margin:0px; padding-right:10px'>ผู้ดำเนินการ : &nbsp;</dt><dd style='float:left; margin:0px; padding-right:10px'><?php echo $user; ?></dd> </dt></dl>    
<?php endif; ?>            
		
        <?php if($order->current_state == 9) : ?>
        	<p class="pull-right top-p">
            	<button type="button" class="btn btn-primary btn-sm" onClick="printBill(<?php echo $id_order; ?>)"><i class="fa fa-print"></i> พิมพ์</button>
                <button type="button" class="btn btn-success btn-sm" onClick="printBarcode(<?php echo $id_order; ?>)"><i class="fa fa-print"></i> พิมพ์บาร์โค้ด</button>
                <button type="button" class="btn btn-default btn-sm" onClick="printPackingList(<?php echo $id_order; ?>)"><i class="fa fa-file-text-o"></i> Picking List</button>
                <button type="button" class="btn btn-info btn-sm" onClick="printAddress(<?php echo $id_order; ?>, <?php echo $order->id_customer; ?>)"><i class="fa fa-file-text-o"></i> พิมพ์ใบปะหน้า</button>
            </p>
            <?php if( $order->payment == 'ออนไลน์' ) : ?>
            	<input type="hidden" name="online" id="online" value="1" />
            <?php endif; ?>
        <?php endif; ?>      
		</div></div>
		<hr style='border-color:#CCC; margin-top: 5px; margin-bottom:15px;' />
		
		<div class='row'>
        <div class='col-lg-12'>
        <table class="table table-bordered">
        <thead>
        	<th style="width:4%; text-align:center">ลำดับ</th>
            <th style="width:10%; text-align:center">บาร์โค้ด</th>
            <th style="width:30%; text-align:center">สินค้า</th>
            <th style="width:10%; text-align:center">ราคา</th>
            <th style="width:8%; text-align:center">จำนวนสั่ง</th>
            <th style="width:8%; text-align:center">จำนวนจัด</th>
            <th style="width:8%; text-align:center">จำนวนที่ได้</th>
            <th style="width:10%; text-align:center">ส่วนลด</th>
            <th style="width:10%; text-align:center">มูลค่า</th>
		</thead>     
        <!------------------------------  Start  ------------------>
           
        <?php if($role != 5 ) : ?>  
		<?php	$qr = dbQuery("SELECT * FROM tbl_order_detail WHERE id_order = ".$id_order);  ?>
        <?php 	$n = 1; $total_amount = 0; $total_discount = 0; $full_amount = 0; $total_qty = 0; $total_valid_qty = 0; $total_temp= 0; ?>
        <?php 	while($rr = dbFetchArray($qr) ) : 													?>
        <?php 		$isVisual = isVisual($rr['id_product_attribute']) == 1 ? TRUE : FALSE; ?>
        <?php 		$order_qty 	= $rr['product_qty']; 												?>
        <?php 		$id_product_attribute = $rr['id_product_attribute']; 						?>
        <?php		$sold 	= $isVisual === TRUE ? FALSE : get_sold_data($id_order, $id_product_attribute);	?>
        <?php		$temp_qty 	= $isVisual === TRUE ? $order_qty : get_temp_qty($id_order, $rr['id_product_attribute']); 	?>
        <?php 		$sold_qty 	= $isVisual === TRUE ? $order_qty : ($sold === FALSE ? 0 : $sold['sold_qty'] );			?>
        <?php 		if($order_qty != $sold_qty || $sold_qty != $temp_qty ) { $hilight = " color: red;"; }else{ $hilight = ""; } ?>
        <?php 		$p_name = $rr['product_reference']. " : ". $rr['product_name']; 			?>
         <?php 		$p_name = substr($p_name, 0, 100); 											?>
         <?php		$discount = $sold == false ? show_discount($rr['reduction_percent'], $rr['reduction_amount']) : show_discount($sold['reduction_percent'], $sold['reduction_amount']); ?>
         
        <tr style="font-size:12px;<?php echo $hilight; ?>">
        	<td align="center"><?php echo $n; ?></td>
            <td align="center"><?php echo $rr['barcode']; ?></td>
            <td><?php echo $p_name; ?></td>
            <td align="center"><?php echo number_format($rr['product_price'],2); ?></td>
            <td align="center"><?php echo number_format($order_qty); ?></td>
            <td align="center"><?php echo number_format($temp_qty); ?></td>
            <td align="center"><?php echo number_format($sold_qty); ?></td>
            <td align="center"><?php echo $discount; ?></td>
            <td align="right"><?php echo number_format($sold == false ? 0.00 : $sold['total_amount'],2); ?></td>
        </tr>
        <?php 
			$total_discount 	+= $sold == false ? 0.00 : $sold['discount_amount'];  
			$total_amount 		+= $sold == false ? 0.00 : $sold['total_amount']; 
			$full_amount 		+= $sold_qty * $rr['product_price']; 
			$total_qty 			+= $order_qty; 
			$total_valid_qty 	+= $sold_qty; 
			$total_temp			+= $temp_qty;
			$n ++; 
			?>
        <?php 	endwhile; ?>
        <?php else: ?>
        <?php $qr = dbQuery("SELECT * FROM tbl_order_detail WHERE id_order = ".$id_order); ?>
        <?php 	$n = 1; $total_amount = 0; $total_discount = 0; $full_amount = 0; $total_qty = 0; $total_valid_qty = 0;  $total_temp = 0; ?>
        <?php 	while($rs = dbFetchArray($qr) ) : ?>
       	<?php		list($qty) = dbFetchArray(dbQuery("SELECT SUM(qty) AS qty FROM tbl_qc WHERE id_product_attribute = ".$rs['id_product_attribute']." AND id_order = ".$id_order." AND valid = 1")); ?>
        <?php			$temp_qty = get_temp_qty($id_order, $rs['id_product_attribute']); ?>
        <?php		if($rs['product_qty'] != $qty || $qty != $temp_qty){ $hilight = " color: red;"; }else{ $hilight = ""; } ?>
         <?php 			$p_name = $rs['product_reference']. " : ". $rs['product_name']; ?>
         <?php 			$p_name = substr($p_name, 0, 100); ?>
         
		<tr style="font-size:12px; <?php echo $hilight; ?>">
        	<td align="center"><?php echo $n; ?></td>
            <td align="center"><?php echo $rs['barcode']; ?></td>
            <td><?php echo $p_name; ?></td>
            <td align="center"><?php echo number_format($rs['product_price'],2); ?></td>
            <td align="center"><?php echo number_format($rs['product_qty']); ?></td>
            <td align="center"><?php echo number_format($temp_qty); ?></td>
            <td align="center"><?php echo number_format($qty); ?></td>
            <td align="center">
			<?php 
				if($rs['reduction_percent'] != 0.00){ 
						$amount = $qty * $rs['product_price'];
						$discount = $rs['reduction_percent']." %"; 
						$discount_amount = $qty * ($rs['product_price'] * ($rs['reduction_percent']/100)); 
					}else if($rs['reduction_amount'] != 0.00){ 
						$amount = $qty * $rs['product_price'];
						$discount = ($qty * $rs['reduction_amount']) . " ฿";
						$discount_amount = $qty * $rs['reduction_amount'];
					}else{
						$discount = "0.00 %";
						$discount_amount = 0;
						$amount = $qty * $rs['product_price']; 
					}
				
				echo $discount;
			?>
            </td>
            <td align="right"><?php echo number_format(($amount - $discount_amount),2); ?></td>
        </tr>
        <?php 	
				$total_amount += $amount; 
				$total_discount += $discount_amount; 
				$full_amount += $amount;  
				$total_qty += $rs['product_qty']; 
				$total_valid_qty += $qty;  
				$total_temp	+= $temp_qty;
				$n++; 
		?>
        <?php 	endwhile; ?>
        <?php endif; ?> 
         <tr>
        	<td colspan="4" align="right">รวม</td>
            <td align="center"><?php echo number_format($total_qty); ?></td>
            <td align="center"><?php echo number_format($total_temp); ?></td>
            <td align="center"><?php echo number_format($total_valid_qty); ?></td>
            <td >ส่วนลดท้ายบิล</td>
            <td align="right"><?php echo number_format($bill_discount, 2); ?></td>
        </tr>
        <tr >
        	<td colspan="4" rowspan="3"><strong>หมายเหตุ : </strong><?php echo $order->comment; ?></td>
            <td colspan="3"><strong>ราคารวม</strong></td><td colspan="2" align="right"><?php echo number_format($full_amount,2); ?></td>
        </tr>
        <tr>
        	<td colspan="3"><strong>ส่วนลด</strong></td><td colspan="2" align="right"><?php echo number_format($total_discount + $bill_discount, 2); ?></td>
        </tr>
         <tr>
        	<td colspan="3"><strong>ยอดเงินสุทธิ</strong></td><td colspan="2" align="right"><?php echo number_format($full_amount - ($total_discount + $bill_discount) ,2); ?></td>
        </tr>
        </table>
        </div>
        </div>
        
        <!------------------------------- end --------------------------->
	<?php else : ?>
    	<h3 style="text-align:center; margin-top:100px; color:red;" ><i class="fa fa-exclamation-triangle fa-2x"></i></h3>
        <h4 style="text-align:center; margin-top:5px; color:red;" >สถานะออเดอร์ไม่ถูกต้อง</h4>
	<?php endif; ?>        
	
<?php else : ?>
<!----------------------------------------------------- แสดงรายการ -------------------------------------------------->
<?php
	if( isset($_POST['from_date']) && $_POST['from_date'] !=""){ setcookie("order_from_date", date("Y-m-d", strtotime($_POST['from_date'])), time() + 3600, "/"); }
	if( isset($_POST['to_date']) && $_POST['to_date'] != ""){ setcookie("order_to_date",  date("Y-m-d", strtotime($_POST['to_date'])), time() + 3600, "/"); }
	$paginator = new paginator();
?>	
<form  method='post' id='form'>
<div class='row'>
	<div class='col-lg-2 col-md-2 col-sm-3 col-sx-3'>
			<label>เงื่อนไข</label>
			<select class='form-control' name='filter' id='filter'>
				<option value='customer' <?php if( isset($_POST['filter']) && $_POST['filter'] =="customer"){ echo "selected"; }else if( isset($_COOKIE['order_filter']) && $_COOKIE['order_filter'] == "customer"){ echo "selected"; } ?> >ลูกค้า</option>
				<option value='reference'<?php if( isset($_POST['filter']) && $_POST['filter'] =="reference"){ echo "selected"; }else if( isset($_COOKIE['order_filter']) && $_COOKIE['order_filter'] == "reference"){ echo "selected"; } ?>>เลขที่เอกสาร</option>
				<option value='sale' <?php if( isset($_POST['filter']) && $_POST['filter'] =="sale"){ echo "selected"; }else if( isset($_COOKIE['order_filter']) && $_COOKIE['order_filter'] == "sale"){ echo "selected"; } ?>>พนักงานขาย</option>
			</select>
		
	</div>	
	<div class='col-lg-3 col-md-3 col-sm-3 col-sx-3'>
    	<label>คำค้น</label>
        <?php 
			$value = '' ; 
			if(isset($_POST['search-text']) && $_POST['search-text'] !="") : 
				$value = $_POST['search-text']; 
			elseif(isset($_COOKIE['order_search-text'])) : 
				$value = $_COOKIE['order_search-text']; 
			endif; 
		?>
		<input class='form-control' type='text' name='search-text' id='search-text' placeholder="ระบุคำที่ต้องการค้นหา" value='<?php echo $value; ?>' />	
	</div>	
	<div class='col-lg-2 col-md-2 col-sm-2 col-sx-2'>
		<label>จากวันที่</label>
            <?php 
				$value = ""; 
				if(isset($_POST['from_date']) && $_POST['from_date'] != "") : 
					$value = date("d-m-Y", strtotime($_POST['from_date'])); 
				elseif( isset($_COOKIE['order_from_date'])) : 
					$value = date("d-m-Y", strtotime($_COOKIE['order_from_date'])); 
				endif; 
				?>
			<input type='text' class='form-control' name='from_date' id='from_date' placeholder="ระบุวันที่" style="text-align:center;"  value='<?php echo $value; ?>'/>
	</div>	
	<div class='col-lg-2 col-md-2 col-sm-2 col-sx-2'>
		<label>ถึงวันที่</label>
            <?php
				$value = "";
				if( isset($_POST['to_date']) && $_POST['to_date'] != "" ) :
				 	$value = date("d-m-Y", strtotime($_POST['to_date'])); 
				 elseif( isset($_COOKIE['order_to_date']) ) :
					$value = date("d-m-Y", strtotime($_COOKIE['order_to_date']));
				 endif;
			?>  
			<input type='test' class='form-control'  name='to_date' id='to_date' placeholder="ระบุวันที่" style="text-align:center" value='<?php echo $value; ?>' />
	</div>
	<div class='col-lg-2 col-md-2 col-sm-2 col-sx-2'>
    	<label style="visibility:hidden">show</label>
		<button class='btn btn-primary btn-block' id='search-btn' type='button'><i class="fa fa-search"></i>&nbsp;ค้นหา</button>
	</div>	
	<div class='col-lg-1 col-md-1 col-sm-1 col-sx-1'>
    	<label style="visibility:hidden">show</label>
		<button type='button' class='btn btn-danger' onclick="window.location.href='controller/billController.php?clear_filter'"><i class='fa fa-refresh'></i>&nbsp;reset</button>
	</div>
</div>
</form>
<hr style='border-color:#CCC; margin-top: 15px; margin-bottom:0px;' />
<?php
		$view = "";
		if(isset($_POST['from_date']) && $_POST['from_date'] != "เลือกวัน"){$from = date('Y-m-d',strtotime($_POST['from_date'])); }else if( isset($_COOKIE['order_from_date'])){ $from = date('Y-m-d',strtotime($_COOKIE['order_from_date'])); }else{ $from = "";} 
		if(isset($_POST['to_date']) && $_POST['to_date'] != "เลือกวัน"){ $to =date('Y-m-d',strtotime($_POST['to_date']));  }else if(  isset($_COOKIE['order_to_date'])){  $to =date('Y-m-d',strtotime($_COOKIE['order_to_date'])); }else{ $to = "";}
		if($from=="" || $to ==""){ $view = getConfig("VIEW_ORDER_IN_DAYS"); 	}
		if($view !=""){
			$date = getLastDays($view);
			$from = $date['from'];
			$to = $date['to'];
		}
		if(isset($_POST['get_rows'])){$get_rows = $_POST['get_rows'];$paginator->setcookie_rows($get_rows);}else if(isset($_COOKIE['get_rows'])){$get_rows = $_COOKIE['get_rows'];}else{$get_rows = 50;}
		
		/****  เงื่อนไขการแสดงผล *****/
		if(isset($_POST['search-text']) && $_POST['search-text'] !="" ) :
			$text = $_POST['search-text'];
			$filter = $_POST['filter'];
			setcookie("order_search-text", $text, time() + 3600, "/");
			setcookie("order_filter",$filter, time() +3600,"/");
			switch( $_POST['filter']) :
				case "customer" :
					$in_cause = "";
					$qs = dbQuery("SELECT id_customer FROM tbl_customer WHERE first_name LIKE'%$text%' OR last_name LIKE'%$text%' GROUP BY id_customer");
					$rs = dbNumRows($qs);
					$i=0;
					if($rs>0) :
						while($i<$rs) :
							list($in) = dbFetchArray($qs);
							$in_cause .="$in";
							$i++;
							if($i<$rs){ $in_cause .=","; 	}
						endwhile;
						$where = "WHERE id_customer IN($in_cause) AND current_state = 9 ORDER BY id_order DESC" ; 
					else :
						$where = "WHERE id_order != NULL";
					endif;
				break;
				case "sale" :
					$in_cause = "";
					$qs = dbQuery("SELECT id_sale FROM tbl_sale LEFT JOIN tbl_employee ON tbl_sale.id_employee = tbl_employee.id_employee WHERE first_name LIKE'%$text%' OR last_name LIKE'%$text%'");
					$rs = dbNumRows($qs);
					$i=0;
					$in ="";
					if($rs>0) :
						while($i<$rs) :
							list($id_sale) = dbFetchArray($qs);
							$in .="$id_sale";
							$i++;
							if($i<$rs){ $in .=","; }
						endwhile;
						$sq = dbQuery("SELECT id_customer FROM tbl_customer WHERE id_sale IN($in)");
						$rs = dbNumRows($sq);
						$n =0;
						while($n<$rs) :
							list($id_customer) = dbFetchArray($sq);
							$in_cause .= "$id_customer";
							$n++;
							if($n<$rs){ $in_cause .= ","; }
						endwhile;
						$where = "WHERE id_customer IN($in_cause) AND current_state = 9 ORDER BY id_order DESC";
					else :
						$where = "WHERE id_order = NULL";
					endif;
				break;
				case "reference" :
				$where = "WHERE reference LIKE'%$text%' AND current_state = 9 ORDER BY reference";
				break;
			endswitch;
		elseif(isset($_COOKIE['order_search-text']) && isset($_COOKIE['order_filter'])) :
			$text = $_COOKIE['order_search-text'];
			$filter = $_COOKIE['order_filter'];
			switch( $filter) :
				case "customer" :
				$in_cause = "";
				$qs = dbQuery("SELECT id_customer FROM tbl_customer WHERE first_name LIKE'%$text%' OR last_name LIKE'%$text%' GROUP BY id_customer");
				$rs = dbNumRows($qs);
				$i=0;
				if($rs>0) :
					while($i<$rs) :
						list($in) = dbFetchArray($qs);
						$in_cause .="$in";
						$i++;
						if($i<$rs){ $in_cause .=","; 	}
					endwhile;
					$where = "WHERE id_customer IN($in_cause) AND current_state = 9 ORDER BY id_order DESC";
					else :
						$where = "WHERE id_order != NULL";
					endif;
				break;
				case "sale" :
					$in_cause = "";
					$qs = dbQuery("SELECT id_sale FROM tbl_sale LEFT JOIN tbl_employee ON tbl_sale.id_employee = tbl_employee.id_employee WHERE first_name LIKE'%$text%' OR last_name LIKE'%$text%'");
					$rs = dbNumRows($qs);
					$i=0;
					$in ="";
					if($rs>0) :
						while($i<$rs) :
							list($id_sale) = dbFetchArray($qs);
							$in .="$id_sale";
							$i++;
							if($i<$rs){ $in .=","; }
						endwhile;
						$sq = dbQuery("SELECT id_customer FROM tbl_customer WHERE id_sale IN($in)");
						$rs = dbNumRows($sq);
						$n =0;
						while($n<$rs) :
							list($id_customer) = dbFetchArray($sq);
							$in_cause .= "$id_customer";
							$n++;
							if($n<$rs){ $in_cause .= ","; }
						endwhile;
						$where = "WHERE id_customer IN($in_cause) AND current_state = 9 ORDER BY id_order DESC";
					else :
						$where = "WHERE id_order = NULL";
					endif;
				break;
				case "reference" :
				$where = "WHERE reference LIKE'%$text%' AND current_state = 9 ORDER BY reference";
				break;
			endswitch;
		else :
			$where = "WHERE (date_add BETWEEN '$from' AND '$to') AND current_state = 9 ORDER BY id_order DESC";
		endif;
?>		

<?php
$paginator = new paginator();
if(isset($_POST['get_rows'])){$get_rows = $_POST['get_rows'];$paginator->setcookie_rows($get_rows);}else if(isset($_COOKIE['get_rows'])){$get_rows = $_COOKIE['get_rows'];}else{$get_rows = 50;}
		$paginator->Per_Page("tbl_order",$where,$get_rows);
		$paginator->display($get_rows,"index.php?content=order_closed");
		$Page_Start = $paginator->Page_Start;
		$Per_Page = $paginator->Per_Page;
?>		
<div class='row'>
<div class='col-sm-12'>
	<table class='table table-striped table-hover'>
    	<thead style='color:#FFF; background-color:#48CFAD;'>
        	<th style='width:5%; text-align:center;'>ลำดับ</th>
			<th style='width:10%;'>เลขที่อ้างอิง</th><th style='width:20%;'>ลูกค้า</th>
            <th style='width:10%; text-align:center;'>ยอดเงิน</th>
			<th style='width:15%; text-align:center;'>เงื่อนไข</th>
			<th style='width:10%; text-align:center;'>สถานะ</th>
			<th style='width:10%;'>พนักงาน</th>
			<th style='width:10%; text-align:center;'>วันที่เพิ่ม</th>
			<th style='width:10%; text-align:center;'>วันที่ปรับปรุง</th>
        </thead>
<?php        
		$result = dbQuery("SELECT id_order,reference,date_add,date_upd,payment,id_customer,id_employee,current_state FROM tbl_order ".$where." LIMIT ".$paginator->Page_Start." , ".$paginator->Per_Page);
		$i=0;
		$n = 1;
		$row = dbNumRows($result);
		if($row>0) :
			while($i<$row) :
				list($id_order,$reference,$date_add,$date_upd,$payment,$id_customer,$id_employee,$current_state) = dbFetchArray($result);
				list($amount) = dbFetchArray(dbQuery("SELECT SUM(total_amount) FROM tbl_order_detail WHERE id_order = '$id_order'"));
				list($cus_first_name,$cus_last_name) = dbFetchArray(dbQuery("SELECT first_name,last_name FROM tbl_customer WHERE id_customer = '$id_customer'"));
				list($em_first_name,$em_last_name) = dbFetchArray(dbQuery("SELECT first_name,last_name FROM tbl_employee WHERE id_employee = '$id_employee'"));
				list($status) = dbFetchArray(dbQuery("SELECT state_name FROM tbl_order_state WHERE id_order_state = '$current_state'"));
				$customer_name = "$cus_first_name $cus_last_name";
				$employee_name = "$em_first_name $em_last_name";	
?>			
			<tr style="font-size:12px;">
				<td align='center' style='cursor:pointer;' onclick="viewOrder(<?php echo $id_order; ?>)"><?php echo $n; ?></td>
				<td style='cursor:pointer;' onclick="viewOrder(<?php echo $id_order; ?>)"><?php echo $reference; ?></td>
				<td style='cursor:pointer;' onclick="viewOrder(<?php echo $id_order; ?>)"><?php echo $customer_name; ?></td>
				<td align='center' style='cursor:pointer;' onclick="viewOrder(<?php echo $id_order; ?>)"><?php echo number_format($amount); ?></td>
				<td align='center' style='cursor:pointer;' onclick="viewOrder(<?php echo $id_order; ?>)"><?php echo $payment; ?></td>
				<td align='center' style='cursor:pointer;' onclick="viewOrder(<?php echo $id_order; ?>)"><?php echo $status; ?></td>
				<td style='cursor:pointer;' onclick="viewOrder(<?php echo $id_order; ?>)"><?php echo $employee_name; ?></td>
				<td align='center' style='cursor:pointer;' onclick="viewOrder(<?php echo $id_order; ?>)"><?php echo thaiDate($date_add); ?></td>
				<td align='center' style='cursor:pointer;' onclick="viewOrder(<?php echo $id_order; ?>)"><?php echo thaiDate($date_upd); ?></td>
			</tr>
<?php		$i++; $n++;  ?>
<?php 	endwhile; ?>		
<?php elseif($row==0) :  ?>
			<tr><td colspan='9' align='center'><h3>-----------------  ไม่มีรายการในช่วงนี้  -----------------</h3></td></tr>
<?php endif; ?>
	</table>
<?php	echo $paginator->display_pages(); ?>
<h3>&nbsp;</h3>
<?php endif; ?>
	<div class='modal fade' id='infoModal' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'>
		<div class='modal-dialog' style="width:500px;">
			<div class='modal-content'>
	  			<div class='modal-header'>
					<button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>
                    <input type="hidden" id="id_customer"/><input type="hidden" id="id_order" />
				 </div>
				 <div class='modal-body' id='info_body'>
                 	
                 </div>
				 <div class='modal-footer'>
                 	<button type="button" class="btn btn-primary btn-sm" onClick="printSelectAddress()"><i class="fa fa-print"></i> พิมพ์</button>
				 </div>
			</div>
		</div>
	</div>
    
<script>

function viewOrder(id_order)
{
	window.location.href = "index.php?content=order_closed&view_detail&id_order="+id_order;
}

function printBill(id_order)
{
	var center = ($(document).width() - 800)/2;
	window.open("controller/billController.php?print_order&id_order="+id_order, "_blank", "width=800, height=900. left="+center+", scrollbars=yes");
}
 
function printBarcode(id_order)
{
	var center = ($(document).width() - 800)/2;
	window.open("controller/billController.php?print_order_barcode&id_order="+id_order, "_blank", "width=800, height=900. left="+center+", scrollbars=yes");
}

function printPackingList(id_order)
{
	window.open("index.php?content=order_closed&print_packing_list&id_order="+id_order, "_blank");
}

function printAddress(id_order, id_customer)
{
	if( $("#online").length ){
		getOnlineAddress(id_order);
	}else{
		getAddressForm(id_order, id_customer);	
	}
}

function getOnlineAddress(id_order)
{
	$.ajax({
		url:"controller/orderController.php?getOnlineAddress",
		type:"POST", cache:"false", data:{"id_order" : id_order },
		success: function(rs){
			var rs = $.trim(rs);
			if( rs == 'noaddress' || isNaN( parseInt(rs) ) ){
				noAddress();
			}else{
				printOnlineAddress(id_order, rs);
			}
		}
	});
}
function getAddressForm(id_order, id_customer)
{
	$.ajax({
		url:"controller/addressController.php?getAddressForm",
		type:"POST",cache: "false", data:{ "id_order" : id_order, "id_customer" : id_customer },
		success: function(rs){
			var rs = $.trim(rs);
			if( rs == 'no_address' ){
				noAddress();
			}else if( rs == 'no_sender' ){
				noSender();
			}else if( rs == 1 ){
				printPackingSheet(id_order, id_customer);
			}else{
				$("#id_customer").val(id_customer);
				$("#id_order").val(id_order);
				$("#info_body").html(rs);
				$("#infoModal").modal("show");
			}
		}
	});
}

function printPackingSheet(id_order, id_customer)
{
	var center = ($(document).width() - 800)/2;
	window.open("controller/addressController.php?printAddressSheet&id_order="+id_order+"&id_customer="+id_customer, "_blank", "width=800, height=900. left="+center+", scrollbars=yes");
}

function printOnlineAddress(id_order, id_address)
{
	var center = ($(document).width() - 800)/2;
	window.open("controller/addressController.php?printOnlineAddressSheet&id_order="+id_order+"&id_address="+id_address, "_blank", "width=800, height=900. left="+center+", scrollbars=yes");
}

function printSelectAddress()
{
	var id_order = $("#id_order").val();
	var id_cus = $("#id_customer").val();
	var id_ad =	$('input[name=id_address]:radio:checked').val();
	var id_sen	= $('input[name=id_sender]:radio:checked').val();
	if( isNaN(parseInt(id_ad)) ){ swal("กรุณาเลือกที่อยู่", "", "warning"); return false; }
	if( isNaN(parseInt(id_sen)) ){ swal("กรุณาเลือกขนส่ง", "", "warning"); return false; }
	$("#infoModal").modal('hide');
	var center = ($(document).width() - 800)/2;
	window.open("controller/addressController.php?printAddressSheet&id_order="+id_order+"&id_customer="+id_cus+"&id_address="+id_ad+"&id_sender="+id_sen, "_blank", "width=800, height=900. left="+center+", scrollbars=yes");
}
function noAddress()
{
	swal("ข้อผิดพลาด", "ไม่พบที่อยู่ของลูกค้า กรุณาตรวจสอบว่าลูกค้ามีที่อยู่ในระบบแล้วหรือยัง", "warning");	
}
function noSender()
{
	swal("ไม่พบผู้จัดส่ง", "ไม่พบรายชื่อผู้จัดส่ง กรุณาตรวจสอบว่าลูกค้ามีการกำหนดชื่อผู้จัดส่งในระบบแล้วหรือยัง", "warning");	
}

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

$("#search-btn").click(function(e){
	load_in();
	var from = $("#from_date").val();
	var to		= $("#to_date").val();
	if(from != "" || to !="")
	{
		if(!isDate(from) || !isDate(to) )
		{
			load_out();
			swal("รูปแบบวันที่ไม่ถูกต้อง");
			return false;
		}else{
			$("#form").submit();
		}
	}else{
		$("#form").submit();
	}
});
</script>