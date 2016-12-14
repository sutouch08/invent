<?php 
require "../../library/config.php";
require "../../library/functions.php";
require "../function/tools.php";
$id_employee = $_COOKIE['user_id'];
//----------------- NEW CODE -------------------//
if( isset( $_GET['addNewAdjust'] ) )
{
	$sc		= 'fail';
	$date		= dbDate($_POST['date']);
	$adj		= new adjust();
	$adj_no 	= $adj->getNewReference($date);
	$ds 		= array(
						'adjust_no'				=> $adj_no,
						'adjust_reference'		=> $_POST['adj_ref'],
						'adjust_date'			=> $date,
						'adjust_note'				=> $_POST['remark'],
						'id_employee'			=> $_POST['id_employee'],
						'adjust_status'			=> 0
						);
	$rs 	= $adj->add($ds);
	if( $rs !== FALSE )
	{
		$sc = $rs;
	}
	echo $sc;
}

	//เพิ่มสินค้าที่จะปรับยอด
if( isset( $_GET['insertDetail'] ) )
{
	require '../function/stock_helper.php';
	$sc 					= 'success';
	$id_adj				= $_POST['id_adjust'];
	$id_pa				= $_POST['id_pa'];
	$id_zone				= $_POST['id_zone'];
	$increase			= $_POST['increase'];
	$decrease			= $_POST['decrease'];
	$stock				= stockInZone($id_pa, $id_zone);
	$allowUnderZero	= allow_under_zero();
	$adj					= new adjust();
	$id_ad				= $adj->isExistsUnsaveDetail($id_adj, $id_pa, $id_zone); //--- if exists will be return id_adjust_detail if not return FALSE;
	if( $id_ad !== FALSE )
	{
		$ds = $adj->getAdjustDetail($id_ad);
		$incQty	= $ds === FALSE ? 0 + $increase : $ds['adjust_qty_add'] + $increase;
		$decQty	= $ds === FALSE ? 0 + $decrease : $ds['adjust_qty_minus'] + $decrease;
		$LastQty	= $incQty - $decQty;
		if( ($stock + $LastQty) < 0 && ! $allowUnderZero)
		{
			$sc = 'ไม่อนุญาติให้สต็อกติดลบ จำนวนที่ปรับลงต้องไม่เกิน '.$stock;
		}
		else
		{
			$qs = $adj->updateDetail($id_ad, $increase, $decrease);
			if( ! $qs )
			{
				$sc = 'เพิ่มรายการไม่สำเร็จ';
			}
			else
			{
				$adj->setStatus($id_adj, 0); //----- Change status to unsaved	
			}
		}			
	}
	else //---- unsave detail not exists insert new one
	{
		$LastQty = $increase - $decrease;
		if( ($stock + $LastQty) < 0 && ! $allowUnderZero)
		{
			$sc = 'ไม่อนุญาติให้สต็อกติดลบ จำนวนที่ปรับลงต้องไม่เกิน '.$stock;	
		}
		else
		{
			$data = array(
							"id_adjust"		=> $id_adj,
							"id_product_attribute" 	=> $id_pa,
							"id_zone"			=> $id_zone,
							"adjust_qty_add"	=> $increase,
							"adjust_qty_minus"	=> $decrease
							);
			$qs = $adj->insertDetail($data);
			if( ! $qs )
			{
				$sc = 'เพิ่มรายการไม่สำเร็จ';
			}
			else
			{
				$adj->setStatus($id_adj, 0); //----- Change status to unsaved	
			}
		}
	}
	echo $sc;
}


if( isset( $_GET['clearFilter'] ) )
{
	$cookie = array('adj_no', 'adj_ref', 'adj_rm', 'adj_vt', 'from', 'to');
	foreach( $cookie as $name )
	{
		deleteCookie($name);
	}
	echo 'done';	
}


//------------------ END NEW CODE ---------------//


///เพิ่มการปรับยอด///
	if(isset($_GET['add'])){
		$adjust_reference = $_POST['adjust_reference'];
		$note = $_POST['note'];
		$adjust_no = newAdjustNO();
		$adjust_date = dbDate($_POST['adjust_date']);
		dbQuery("INSERT INTO tbl_adjust(adjust_no,adjust_reference,adjust_date,adjust_note,id_employee) VALUES ('$adjust_no','$adjust_reference','$adjust_date','$note','$id_employee')");
		list($id_adjust) = dbFetchArray(dbQuery("SELECT id_adjust from tbl_adjust where adjust_no = '$adjust_no'"));
		header("location: ../index.php?content=ProductAdjust&add=y&id_adjust=$id_adjust");
	}


	//โหลดยอด diff
	if(isset($_GET['loaddiff'])){
		$id_adjust = $_POST['id_adjust'];
		for($loop=1;$loop<=$_POST["hdnCount"];$loop++){
			if(isset($_POST["chkDel$loop"])){
			$id_diff = $_POST["chkDel$loop"];
				if($id_diff != ""){
					dbQuery("UPDATE tbl_diff SET status_diff = '1' where id_diff = '$id_diff'");
					list($id_zone,$id_product_attribute,$qty_add,$qty_minus) = dbFetchArray(dbQuery("select id_zone,id_product_attribute,qty_add,qty_minus from tbl_diff where id_diff = '$id_diff'"));
					dbQuery("INSERT INTO tbl_adjust_detail(id_adjust,id_product_attribute,id_zone,adjust_qty_add,adjust_qty_minus,status_adjust)VALUES('$id_adjust','$id_product_attribute','$id_zone','$qty_add','$qty_minus','$id_diff')");
				}
			}
		}
		dbQuery("UPDATE tbl_adjust SET adjust_status = '0'  WHERE id_adjust = '$id_adjust'");
		header("location: ../index.php?content=ProductAdjust&add=y&id_adjust=$id_adjust");
	}
	
	
	//ลบรายการสินค้าที่ปรับยอด
	if(isset($_GET['drop_adjust'])){
		$id_adjust = $_GET['id_adjust'];
		$id_adjust_detail = $_GET['id_adjust_detail'];
		list($adjust_no) = dbFetchArray(dbQuery("SELECT adjust_no from tbl_adjust where id_adjust = '$id_adjust'"));
		list($status_adjust,$status_up,$id_zone,$id_product_attribute,$adjust_qty_add,$adjust_qty_minus) = dbFetchArray(dbQuery("select status_adjust,status_up,id_zone,id_product_attribute,adjust_qty_add,adjust_qty_minus from tbl_adjust_detail where id_adjust_detail = '$id_adjust_detail'"));
		list($id_warehouse) = dbFetchArray(dbQuery("select id_warehouse from tbl_zone where id_zone = '$id_zone'"));
		list($adjust_date) = dbFetchArray(dbQuery("select adjust_date from tbl_adjust where id_adjust = '$id_adjust'"));
		if($status_up == "0"){
			dbQuery("DELETE FROM tbl_adjust_detail where id_adjust_detail = '$id_adjust_detail'");
		}else{
			$sum_qty_adjust = $adjust_qty_add + ($adjust_qty_minus *-1);
			list($id_stock,$qty) = dbFetchArray(dbQuery("SELECT id_stock,qty FROM tbl_stock WHERE id_zone = '$id_zone' and id_product_attribute = '$id_product_attribute'"));
			if($id_stock != "")
			{
				$sumqty = $qty + ($sum_qty_adjust * -1);
				dbQuery("UPDATE tbl_stock SET qty = ".$sumqty." WHERE id_stock = ".$id_stock);
			}
			else
			{
				$sumqty = ($sum_qty_adjust * -1);
				dbQuery("INSERT INTO tbl_stock (id_zone, id_product_attribute, qty) VALUES (".$id_zone.", ".$id_product_attribute.", ".$sumqty.")");	
			}
			dbQuery("DELETE FROM tbl_stock_movement WHERE reference = '".$adjust_no."' AND id_product_attribute = ".$id_product_attribute." AND id_zone = ".$id_zone);
			dbQuery("DELETE FROM tbl_adjust_detail where id_adjust_detail = '$id_adjust_detail'");
		}
		if($status_adjust != 0)
		{
			dbQuery("UPDATE tbl_diff SET status_diff = '0' where id_diff = '$status_adjust'");
		}
		dropstockZero($id_product_attribute,$id_zone);
		header("location: ../index.php?content=ProductAdjust&add=y&id_adjust=$id_adjust");
	}
	
	
	//แก้ไขรายการสินค้าที่ปรับยอด
	if(isset($_GET['edit_detail'])){
		$id_adjust = $_POST['id_adjust'];
		$id_adjust_detail = $_POST['id_adjust_detail'];
		$barcode = $_POST['barcode'];
		$barcode_zone = $_POST['barcode_zone'];
		$zone_name = $_POST['zone_name'];
		$adjust_qty_add = $_POST['adjust_qty_add'];
		$adjust_qty_minus = $_POST['adjust_qty_minus'];
		$id_warehouse = $_POST['id_warehouse'];
		list($id_product_attribute) = dbFetchArray(dbQuery("SELECT id_product_attribute FROM tbl_product_attribute where barcode = '$barcode'"));
		list($id_zone) = dbFetchArray(dbQuery("SELECT id_zone FROM tbl_zone WHERE (barcode_zone = '$barcode_zone' or zone_name = '$zone_name') and id_warehouse = '$id_warehouse'"));
		if($id_product_attribute == ""){
			$message = "ไม่มีสินค้านี้กรุณาตรวจสอบบาร์โค้ดสินค้าใหม่";
			header("location: ../index.php?content=ProductAdjust&add=y&id_adjust=$id_adjust&message=$message");
		}else if($id_zone == ""){
			$message = "ไม่มีโซนนี้กรุณาตรวจสอบ คลัง บาร์โค้ดหรือชื่อโซน";
			header("location: ../index.php?content=ProductAdjust&add=y&id_adjust=$id_adjust&message=$message");
		}else{
			dbQuery("UPDATE tbl_adjust_detail SET id_product_attribute = '$id_product_attribute' , id_zone = '$id_zone' , adjust_qty_add = '$adjust_qty_add' , adjust_qty_minus = '$adjust_qty_minus' WHERE id_adjust_detail = '$id_adjust_detail'");
		}
		header("location: ../index.php?content=ProductAdjust&add=y&id_adjust=$id_adjust");
	}
	//ปรับยอด
	if(isset($_GET['adjust'])){
		$id_adjust = $_GET['id_adjust'];
		list($adjust_date) = dbFetchArray(dbQuery("select adjust_date from tbl_adjust where id_adjust = '$id_adjust'"));
		list($adjust_no) = dbFetchArray(dbQuery("SELECT adjust_no FROM tbl_adjust WHERE id_adjust = '$id_adjust'"));
		$result = dbQuery("SELECT id_adjust_detail,id_product_attribute,barcode,reference,id_warehouse,warehouse_name,id_zone,barcode_zone,zone_name,adjust_qty_add,adjust_qty_minus,status_adjust FROM adjust_datail_table where id_adjust = '$id_adjust' and status_up = '0'");
		$i=0;
		$row = dbNumRows($result);
		while($i<$row){
		list($id_adjust_detail, $id_product_attribute, $barcode, $reference, $id_warehouse, $warehouse_name, $id_zone ,$barcode_zone ,$zone_name ,$adjust_qty_add ,$adjust_qty_minus ,$status_adjust) = dbFetchArray($result);	
		$sum_qty_adjust = $adjust_qty_add + ($adjust_qty_minus * -1);
		list($id_stock,$qty) = dbFetchArray(dbQuery("SELECT id_stock,qty FROM tbl_stock where id_zone = '$id_zone' and id_product_attribute = '$id_product_attribute'"));
		if($id_stock != ""){
		$sumqty = $qty + $sum_qty_adjust;
		dbQuery("UPDATE tbl_stock SET qty = '$sumqty' where id_stock = '$id_stock'");
		dbQuery("UPDATE tbl_diff SET status_diff = '2' where id_diff = '$status_adjust'");
		if($sum_qty_adjust > "0" ){
			stock_movement("in",7,$id_product_attribute,$id_warehouse,$sum_qty_adjust, $adjust_no,$adjust_date, $id_zone);
		}else if($sum_qty_adjust < "0"){
			$sum_qty_adjust1 = $sum_qty_adjust * (-1);
			stock_movement("out",8,$id_product_attribute,$id_warehouse,$sum_qty_adjust1, $adjust_no,$adjust_date, $id_zone);
		}
		}else{
			dbQuery("INSERT INTO tbl_stock (id_zone,id_product_attribute,qty) VALUES ('$id_zone','$id_product_attribute','$sum_qty_adjust')");
			if($sum_qty_adjust > "0" ){
				stock_movement("in",7,$id_product_attribute,$id_warehouse,$sum_qty_adjust, $adjust_no,$adjust_date,$id_zone);
			}else if($sum_qty_adjust < "0"){
				$sum_qty_adjust1 = $sum_qty_adjust * (-1);
				stock_movement("out",8,$id_product_attribute,$id_warehouse,$sum_qty_adjust1, $adjust_no,$adjust_date, $id_zone);
			}
		}
		dropstockZero($id_product_attribute,$id_zone);
		$i++;
		}
		dbQuery("UPDATE tbl_adjust SET adjust_status = '1' where id_adjust = '$id_adjust'");
		dbQuery("UPDATE tbl_adjust_detail SET status_up = '1' where id_adjust = '$id_adjust'");
		header("location: ../index.php?content=ProductAdjust&view_detail=y&id_adjust=$id_adjust");
	}
	//ลบการปรับยอด
	if(isset($_GET['drop'])){
		$id_adjust = $_GET['id_adjust'];
		list($adjust_date) = dbFetchArray(dbQuery("select adjust_date from tbl_adjust where id_adjust = '$id_adjust'"));
		list($adjust_no) = dbFetchArray(dbQuery("SELECT adjust_no from tbl_adjust where id_adjust = '$id_adjust'"));
		$result = dbQuery("SELECT id_adjust_detail,status_adjust,status_up,id_zone,id_product_attribute,adjust_qty_add,adjust_qty_minus FROM adjust_datail_table where id_adjust = '$id_adjust'");
		$i=0;
		$row = dbNumRows($result);
		while($i<$row){
		list($id_adjust_detail, $status_adjust,$status_up,$id_zone,$id_product_attribute,$adjust_qty_add,$adjust_qty_minus) = dbFetchArray($result);
		list($id_warehouse) = dbFetchArray(dbQuery("select id_warehouse from tbl_zone where id_zone = '$id_zone'"));
			if($status_adjust == "0"){
			if($status_up == "0"){
				dbQuery("DELETE FROM tbl_adjust_detail where id_adjust_detail = '$id_adjust_detail'");
			}else{
				$sum_qty_adjust = $adjust_qty_add - $adjust_qty_minus;
				list($id_stock,$qty) = dbFetchArray(dbQuery("SELECT id_stock,qty FROM tbl_stock WHERE id_zone = '$id_zone' and id_product_attribute = '$id_product_attribute'"));
				$sumqty = $qty - $sum_qty_adjust;
				dbQuery("UPDATE tbl_stock SET qty = '$sumqty' WHERE id_stock = '$id_stock'");
				if($sum_qty_adjust < "0" ){
					$sum_qty_adjust1 = $sum_qty_adjust * (-1);
					stock_movement("in",7,$id_product_attribute,$id_warehouse,$sum_qty_adjust1, $adjust_no,$adjust_date);
				}else if($sum_qty_adjust > "0"){
					stock_movement("out",8,$id_product_attribute,$id_warehouse,$sum_qty_adjust, $adjust_no,$adjust_date);
				}
				dbQuery("DELETE FROM tbl_adjust_detail where id_adjust_detail = '$id_adjust_detail'");
			}
		}else{
			if($status_up == "0"){
				dbQuery("DELETE FROM tbl_adjust_detail where id_adjust_detail = '$id_adjust_detail'");
			}else{
				$sum_qty_adjust = $adjust_qty_add - $adjust_qty_minus;
				list($id_stock,$qty) = dbFetchArray(dbQuery("SELECT id_stock,qty FROM tbl_stock WHERE id_zone = '$id_zone' and id_product_attribute = '$id_product_attribute'"));
				$sumqty = $qty - $sum_qty_adjust;
				dbQuery("UPDATE tbl_stock SET qty = '$sumqty' WHERE id_stock = '$id_stock'");
				if($sum_qty_adjust < "0" ){
					$sum_qty_adjust1 = $sum_qty_adjust * (-1);
					stock_movement("in",7,$id_product_attribute,$id_warehouse,$sum_qty_adjust1, $adjust_no,$adjust_date);
				}else if($sum_qty_adjust > "0"){
					stock_movement("out",8,$id_product_attribute,$id_warehouse,$sum_qty_adjust, $adjust_no,$adjust_date);
				}
				dbQuery("DELETE FROM tbl_adjust_detail where id_adjust_detail = '$id_adjust_detail'");
			}
			dbQuery("UPDATE tbl_diff SET status_diff = '0' where id_diff = '$status_adjust'");
		}
		dropstockZero($id_product_attribute,$id_zone);
		$i++;
		}
		dbQuery("DELETE FROM tbl_adjust where id_adjust = '$id_adjust'");
		
		header("location: ../index.php?content=ProductAdjust");
	}
?>