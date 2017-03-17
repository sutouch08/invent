<?php
	$from = fromDate($_GET['from']);
	$to	= toDate($_GET['to']);
	$vat	= getConfig('VAT'); //---- 7
	$pred = date("dmY", strtotime($from)) .' - '. date("dmY", strtotime($to));
	//$pGroup	= getConfig('ITEMS_GROUP');
	$excel	= new PHPExcel();
	
	$excel->getProperties()->setCreator("Samart Invent")
							 ->setLastModifiedBy("Samart Invent")
							 ->setTitle("Report Sold Deep Analyz")
							 ->setSubject("Report Sold Deep Analyz")
							 ->setDescription("This file was generate by Smart invent web application via PHPExcel v.1.8")
							 ->setKeywords("Samart Invent")
							 ->setCategory("Stock Report");
	$excel->setActiveSheetIndex(0);
	$excel->getActiveSheet()->setTitle('รายงานการรับสินค้าเข้า');
	
	//---------  หัวตาราง  ------------//
	$excel->getActiveSheet()->setCellValue('A1', 'sold_date');
	$excel->getActiveSheet()->setCellValue('B1', 'product');
	$excel->getActiveSheet()->setCellValue('C1', 'color');
	$excel->getActiveSheet()->setCellValue('D1', 'size');
	$excel->getActiveSheet()->setCellValue('E1', 'attribute');
	$excel->getActiveSheet()->setCellValue('F1', 'category');
	$excel->getActiveSheet()->setCellValue('G1', 'cost_ex');
	$excel->getActiveSheet()->setCellValue('H1', 'cost_inc');
	$excel->getActiveSheet()->setCellValue('I1', 'price_ex');
	$excel->getActiveSheet()->setCellValue('J1', 'price_inc');
	$excel->getActiveSheet()->setCellValue('K1', 'sell_ex');
	$excel->getActiveSheet()->setCellValue('L1', 'sell_inc');
	$excel->getActiveSheet()->setCellValue('M1', 'qty');
	$excel->getActiveSheet()->setCellValue('N1', 'discount');
	$excel->getActiveSheet()->setCellValue('O1', 'amount_ex');
	$excel->getActiveSheet()->setCellValue('P1', 'amount_inc');
	$excel->getActiveSheet()->setCellValue('Q1', 'type');
	$excel->getActiveSheet()->setCellValue('R1', 'customer');
	$excel->getActiveSheet()->setCellValue('S1', 'saleman');
	$excel->getActiveSheet()->setCellValue('T1', 'area');
	$excel->getActiveSheet()->setCellValue('U1', 'group');
	$excel->getActiveSheet()->setCellValue('V1', 'emp');
	$excel->getActiveSheet()->setCellValue('W1', 'total_cost_ex');
	$excel->getActiveSheet()->setCellValue('X1', 'total_cost_inc');
	$excel->getActiveSheet()->setCellValue('Y1', 'margin_ex');
	$excel->getActiveSheet()->setCellValue('Z1', 'margin_inc');
	
	
	$qs = dbQuery("SELECT * FROM tbl_order_detail_sold WHERE id_role IN(".$role_in.") AND date_upd > '".$from."' AND date_upd < '".$to."' ORDER BY id_product ASC");
	
	if( dbNumRows($qs) > 0 )
	{
		$row	= 2;  //------ เริ่มต้นแถวที่ 2
		while( $rs = dbFetchArray($qs) )
		{
			$pa	= getProductAttribute($rs['id_product_attribute']);  //------  return as array $pa['id_color'], $pa['id_size'], $pa['id_attribute']
			$con	= $rs['id_role'] == 5 ? 'ฝากขาย' : getPaymentText($rs['id_order']);
			$y		= date('Y', strtotime($rs['date_upd']) );
			$m		= date('m', strtotime($rs['date_upd']) );
			$d		= date('d', strtotime($rs['date_upd']) );
			$date = PHPExcel_Shared_Date::FormattedPHPToExcel($y, $m, $d);
			$excel->getActiveSheet()->setCellValue('A'.$row, $date);  //----- วันที่
			$excel->getActiveSheet()->setCellValue('B'.$row, get_product_code($rs['id_product'])); //------ รุ่นสินค้า
			$excel->getActiveSheet()->setCellValue('C'.$row, get_color_code($pa['id_color']) ); //-----  สี
			$excel->getActiveSheet()->setCellValue('D'.$row, get_size_name($pa['id_size']) ); //------- Size
			$excel->getActiveSheet()->setCellValue('E'.$row, get_attribute_name($pa['id_attribute']) ); //----- คุณลักษระอื่นๆ
			$excel->getActiveSheet()->setCellValue('F'.$row, getDefaultCategoryName($rs['id_product']) ); //----- กลุ่มสินค้า
			$excel->getActiveSheet()->setCellValue('G'.$row, $rs['cost'] ); //-----  ทุนไม่รวม VAT
			$excel->getActiveSheet()->setCellValue('H'.$row, addVAT($rs['cost'], $vat) ); //----- ทุนรวม VAT
			$excel->getActiveSheet()->setCellValue('I'.$row, removeVAT($rs['product_price'], $vat) ); //----- ราคาป้าย ไม่ราม VAT
			$excel->getActiveSheet()->setCellValue('J'.$row, $rs['product_price'] ); //----- ราคาป้าย
			$excel->getActiveSheet()->setCellValue('K'.$row, removeVAT($rs['final_price'], $vat) ); //----- ขายไม่รวม VAT
			$excel->getActiveSheet()->setCellValue('L'.$row, $rs['final_price'] ); //-----  ขาย
			$excel->getActiveSheet()->setCellValue('M'.$row, $rs['sold_qty'] ); //----- จำนวนขาย
			$excel->getActiveSheet()->setCellValue('N'.$row, $rs['discount_amount'] ); //----- ส่วนลดรวม
			$excel->getActiveSheet()->setCellValue('O'.$row, removeVAT($rs['total_amount'], $vat) ); //----- มูลค่าขายไม่รวม VAT
			$excel->getActiveSheet()->setCellValue('P'.$row, $rs['total_amount'] ); //----- มูลค่าขาย
			$excel->getActiveSheet()->setCellValue('Q'.$row, $con); //---- ช่องทางการขาย
			$excel->getActiveSheet()->setCellValue('R'.$row, customer_name($rs['id_customer']) ); //--- ร้านค้า
			$excel->getActiveSheet()->setCellValue('S'.$row, sale_name($rs['id_sale']) ); //--- พนักงานขาย
			$excel->getActiveSheet()->setCellValue('T'.$row, customerDefaultGroupName($rs['id_customer']) ); //---- เขตการขาย
			$excel->getActiveSheet()->setCellValue('U'.$row, getProductGroupName($rs['id_product']));
			$excel->getActiveSheet()->setCellValue('V'.$row, employee_name($rs['id_employee'])); //---- พนักงานผู้ทำรายการ
			$excel->getActiveSheet()->setCellValue('W'.$row, $rs['total_cost']); //---- ต้นทุนรวม (ไม่รวม VAT)
			$excel->getActiveSheet()->setCellValue('X'.$row, addVAT($rs['total_cost'], $vat)); //---- ต้นทุนรวม (รวม VAT)
			$excel->getActiveSheet()->setCellValue('Y'.$row, removeVAT($rs['total_amount'], $vat) - $rs['total_cost'] ); //---- กำไรขั้นต้น (ไม่รวม VAT)
			$excel->getActiveSheet()->setCellValue('Z'.$row, $rs['total_amount'] - addVAT($rs['total_cost'], $vat) ); //---- กำไรขั้นต้น (รวม VAT )
			
			$row++;			
			
		}//----- end while 		
		
		$excel->getActiveSheet()->getStyle('A2:A'.$row)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
		$excel->getActiveSheet()->getStyle('G2:L'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
		$excel->getActiveSheet()->getStyle('M2:M'.$row)->getNumberFormat()->setFormatCode('#,##0');
		$excel->getActiveSheet()->getStyle('N2:P'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
		$excel->getActiveSheet()->getStyle('W2:Z'.$row)->getNumberFormat()->setFormatCode('#,##0.00');
		
	}

	
	setToken($_GET['token']);
	$file_name = "รายงานวิเคราะห์ขายแบบละเอียด".$pred.".xlsx";
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); /// form excel 2007 XLSX
	header('Content-Disposition: attachment;filename="'.$file_name.'"');
	$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
	$writer->save('php://output');	


?>