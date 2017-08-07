<?php
/** Error reporting */
error_reporting(0);

/** PHPExcel */
require_once 'Classes/PHPExcel.php';

/** PHPExcel_IOFactory */
require_once 'Classes/PHPExcel/IOFactory.php';


class Export {

	
	public function startExport($data) {
	
		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		
		// Set properties
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
								 ->setLastModifiedBy("Maarten Balliauw")
								 ->setTitle("Office 2007 XLSX Test Document")
								 ->setSubject("Office 2007 XLSX Test Document")
								 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
								 ->setKeywords("office 2007 openxml php")
								 ->setCategory("Test result file");
		
		
		$arrayCol = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ");
		
		$count = count($data);  //行数

		$pageSize = 60000;	//每一张表有多少数据
		$pageCount = 0;  //当前所在的页码
		$objPHPExcel->setActiveSheetindex($pageCount);  
		$currentActiveSheet = $objPHPExcel->getActiveSheet();
		$objPHPExcel->getActiveSheet()->setTitle('结果表'.($pageCount+1));
		for($i=0;$i<$count;$i++) {
			if($i>0 && $i%$pageSize==0) {
				$objWorksheet1 = $objPHPExcel->createSheet();  //如果数据大于每一张表的数据量时,新建一个表
				$currentActiveSheet = $objWorksheet1;    //重新设置当前活动表
				$pageCount++;
				$currentActiveSheet->setTitle("结果表".($pageCount+1));	
			}
			$currentRow = array_values($data[$i]);  //当前行
			$currentRowCount = count($data[$i]);  //当前行总列数
//            var_dump($currentRowCount);die;
			for($j=0;$j<$currentRowCount;$j++) {
				$col = $arrayCol[$j];
				$currentCount = $i-$pageCount*$pageSize;
                $currentActiveSheet->setCellValue("$col".($currentCount+1),$currentRow[$j]);
//                var_dump($currentRow[$j]);
//                var_dump("$col".($currentCount+1),$currentRow[$j]);
			}
		}
//die;
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
        ob_end_clean();
		// Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.time().'.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output'); 
		exit;
	}
	
	function inCode($str) {
		return iconv('gbk', 'utf-8', $str);
	}
	
}
?>