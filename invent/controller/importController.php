<?php
require "../../library/config.php";
require "../../library/functions.php";
require "../function/tools.php";
require "../../library/class/class.upload.php";
 require "../../library/class/PHPExcel.php";

function getIdZoneByBarcode($barcode)
{
	$sc = FALSE;
	$qs = dbQuery("SELECT id_zone FROM tbl_zone WHERE barcode_zone = '".$barcode."'");
	if(dbNumRows($qs) == 1 )
	{
		list( $sc ) = dbFetchArray($qs);	
	}
	return $sc;
}

function getIdProductAttributeByItemCode($code)
{
	$sc = FALSE;
	$qs = dbQuery("SELECT id_product_attribute FROM tbl_product_attribute WHERE reference = '".$code."'");
	if( dbNumRows($qs) == 1 )
	{
		list( $sc ) = dbFetchArray($qs);
	}
	return $sc;
}

function isExistsStockZone($id_zone, $id_pa)
{
	$sc = FALSE;
	$qs = dbQuery("SELECT id_stock FROM tbl_stock WHERE id_zone = ".$id_zone." AND id_product_attribute = ".$id_pa);
	if( dbNumRows($qs) > 0 )
	{
		$sc = TRUE;
	}
	return $sc;
}

if( isset( $_GET['importStockZone'] ) )
{
	$sc = '';
	$suc = 0;
	$err = 0;
	$skip = 0;
	$skr = array();
	$file = isset( $_FILES['uploadFile'] ) ? $_FILES['uploadFile'] : FALSE;
	 $file_path 	= "../../upload/";
    $upload	= new upload($file);
    if($upload->uploaded)
    {
	$upload->file_new_name_body = 'importItem';
	$upload->file_overwrite     = TRUE;
	$upload->auto_create_dir    = FALSE;
	
	$upload->process($file_path);
	if( ! $upload->processed)
	{
            $sc = $upload->error;
        }else{
            
            $excel = PHPExcel_IOFactory::load($upload->file_dst_pathname);
            $max = $excel->getActiveSheet()->getHighestRow();
            
            $row = 2; 
            while($row <= $max)
			{			
				set_time_limit(60);			
				$zone = 	trim($excel->getActiveSheet()->getCell('A'.$row)->getValue());
				$item = trim($excel->getActiveSheet()->getCell('B'.$row)->getValue());
             	$idZone 	= getIdZoneByBarcode($zone);
                $id_pa 	= getIdProductAttributeByItemCode($item);
                $qty   	= $excel->getActiveSheet()->getCell('C'.$row)->getValue();
				

				if( $idZone !== FALSE && $id_pa !== FALSE )
				{
					if( isExistsStockZone($idZone, $id_pa) )
					{
						$qs = dbQuery("UPDATE tbl_stock SET qty = qty + ".$qty." WHERE id_zone = ".$idZone." AND id_product_attribute = ".$id_pa);
					}
					else
					{
						$qs = dbQuery("INSERT INTO tbl_stock (id_zone, id_product_attribute, qty) VALUES (".$idZone.", ".$id_pa.", ".$qty.")");
					}
					if( ! $qs )
					{
						$err++;
					}
					else
					{
						$suc++;
					}
				}
				else
				{
					$arr = array("zone" => $zone, "id_zone" => $idZone, "item" => $item, "id_pa" => $id_pa, "qty" => $qty);
					array_push($skr, $arr);
					$skip++;
				}

               	$row++; 
         }
		 $sc = array( "imported" => $suc, "total" => ($max -1), "fail" => $err, "skip" => $skip, "SkipItem" => $skr);
		 $sc = json_encode($sc);
           // $sc = 'imported: '.$suc.' of '.$max.' , fail: '.$err;
       }
    }
    $upload->clean();
   echo $sc;	
}


?>