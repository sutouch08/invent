<?php

require "../../../../library/config.php";
require "../../../../library/functions.php";
require "../../../function/tools.php";
require "../../../function/report_helper.php";


if( isset( $_GET['supportProductDeepAnalyz'] ) )
{
	$role	= 7;  //-- เบิกอภินันท์
	$from = fromDate($_GET['from']);
	$to	= toDate($_GET['to']);
	$vat	= getConfig('VAT');
	$pred = date("dmY", strtotime($from)) .' - '. date("dmY", strtotime($to));
	$excel	= new PHPExcel();
	
	$excel->getProperties()->setCreator("Samart Invent")
							 ->setLastModifiedBy("Samart Invent")
							 ->setTitle("Report Support Deep Analyz")
							 ->setSubject("Report Support Deep Analyz")
							 ->setDescription("This file was generate by Smart invent web application via PHPExcel v.1.8")
							 ->setKeywords("Samart Invent")
							 ->setCategory("MarKeting Report");
	$excel->setActiveSheetIndex(0);
	$excel->getActiveSheet()->setTitle('รายงานอภินันท์แบบละเอียด');
	/*
	$excel->getActiveSheet()->setCellValue('A1', 'รายงานวิเคราะห์ขายแบบละเอียด');
	$excel->getActiveSheet()->setCellValue('A2', 'วันที่');
	$excel->getActiveSheet()->setCellValue('B2', thaiDate($from, '/').' - '.thaiDate($to, '/') );
	$excel->getActiveSheet()->setCellValue('A3', 'ช่องทางการขาย');
	$excel->getActiveSheet()->setCellValue('B3', $roleName);
	$excel->getActiveSheet()->setCellValue('D3', 'VAT');
	$excel->getActiveSheet()->setCellValue('E3', $vat.' %');
	*/
	
	//---------  หัวตาราง  ------------//
	$excel->getActiveSheet()->setCellValue('A1', 'sold_date'); //--- วันที่
	$excel->getActiveSheet()->setCellValue('B1', 'product');
	$excel->getActiveSheet()->setCellValue('C1', 'color');
	$excel->getActiveSheet()->setCellValue('D1', 'size');
	$excel->getActiveSheet()->setCellValue('E1', 'attribute');
	$excel->getActiveSheet()->setCellValue('F1', 'category');
	$excel->getActiveSheet()->setCellValue('G1', 'cost_ex');
	$excel->getActiveSheet()->setCellValue('H1', 'cost_inc');
	$excel->getActiveSheet()->setCellValue('I1', 'price_ex');
	$excel->getActiveSheet()->setCellValue('J1', 'price_inc');
	$excel->getActiveSheet()->setCellValue('K1', 'qty');
	$excel->getActiveSheet()->setCellValue('L1', 'amount_ex');
	$excel->getActiveSheet()->setCellValue('M1', 'amount_inc');
	$excel->getActiveSheet()->setCellValue('N1', 'receiver'); //--- ผู้รับ(บุคคลภายนอก)
	$excel->getActiveSheet()->setCellValue('O1', 'budget_owner'); //---- ผู้เบิก(เจ้าของงบ)
	$excel->getActiveSheet()->setCellValue('P1', 'operater'); //-- ผู้ทำรายการ
	
	$qs = dbQuery("SELECT * FROM tbl_order_detail_sold WHERE id_role IN(".$role.") AND date_upd > '".$from."' AND date_upd < '".$to."' ORDER BY id_product ASC");
	
	if( dbNumRows($qs) > 0 )
	{
		$row	= 2;  //------ เริ่มต้นแถวที่ 2
		while( $rs = dbFetchArray($qs) )
		{
			$pa	= getProductAttribute($rs['id_product_attribute']);  //------  return as array $pa['id_color'], $pa['id_size'], $pa['id_attribute']
			$y		= date('Y', strtotime($rs['date_upd']) );
			$m		= date('m', strtotime($rs['date_upd']) );
			$d		= date('d', strtotime($rs['date_upd']) );
			$ra 	= dbFetchObject(dbQuery("SELECT * FROM tbl_order_support WHERE id_order = ".$rs['id_order']));
			
			$date = PHPExcel_Shared_Date::FormattedPHPToExcel($y, $m, $d);
			$excel->getActiveSheet()->setCellValue('A'.$row, $date);  //----- วันที่
			$excel->getActiveSheet()->setCellValue('B'.$row, get_product_code($rs['id_product'])); //------ รุ่นสินค้า
			$excel->getActiveSheet()->setCellValue('C'.$row, get_color_code($pa['id_color']) ); //-----  สี
			$excel->getActiveSheet()->setCellValue('D'.$row, get_size_name($pa['id_size']) ); //------- Size
			$excel->getActiveSheet()->setCellValue('E'.$row, get_attribute_name($pa['id_attribute']) ); //----- คุณลักษระอื่นๆ
			$excel->getActiveSheet()->setCellValue('F'.$row, getDefaultCategoryName($rs['id_product']) ); //----- กลุ่มสินค้า
			$excel->getActiveSheet()->setCellValue('G'.$row, removeVAT($rs['cost'], $vat) ); //-----  ทุนไม่รวม VAT
			$excel->getActiveSheet()->setCellValue('H'.$row, $rs['cost'] ); //----- ทุนรวม VAT
			$excel->getActiveSheet()->setCellValue('I'.$row, removeVAT($rs['product_price'], $vat) ); //----- ราคาป้าย ไม่ราม VAT
			$excel->getActiveSheet()->setCellValue('J'.$row, $rs['product_price'] ); //----- ราคาป้าย
			$excel->getActiveSheet()->setCellValue('K'.$row, $rs['sold_qty'] ); //----- จำนวนขาย
			$excel->getActiveSheet()->setCellValue('L'.$row, removeVAT($rs['total_amount'], $vat) ); //----- มูลค่าขายไม่รวม VAT
			$excel->getActiveSheet()->setCellValue('M'.$row, $rs['total_amount'] ); //----- มูลค่าขาย
			$excel->getActiveSheet()->setCellValue('N'.$row, customer_name($rs['id_customer']) ); //--- ผู้รับ(บุคคลภายนอก)
			$excel->getActiveSheet()->setCellValue('O'.$row, employee_name($rs['id_employee'])); //---- ผู้เบิก(เจ้าของงบ)
			$excel->getActiveSheet()->setCellValue('P'.$row, employee_name($ra->id_user)); //---- พนักงานผู้ทำรายการ
			
			$row++;			
			
		}//----- end while 		
		
		$excel->getActiveSheet()->getStyle('A2:A'.$row)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
		$excel->getActiveSheet()->getStyle('G2:J'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
		$excel->getActiveSheet()->getStyle('K2:K'.$row)->getNumberFormat()->setFormatCode('#,##0');
		$excel->getActiveSheet()->getStyle('L2:M'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
		
	}
	
	setToken($_GET['token']);
	$file_name = "รายงานวิเคราะห์อภินันท์แบบละเอียด".$pred.".xlsx";
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); /// form excel 2007 XLSX
	header('Content-Disposition: attachment;filename="'.$file_name.'"');
	$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
	$writer->save('php://output');	
}

?>