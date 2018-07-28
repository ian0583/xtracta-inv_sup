<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$suppliers = explode( "\n", file_get_contents( 'suppliernames.txt' ) );
$headers = str_getcsv( array_shift( $suppliers ) );
$data = array();
foreach ( $suppliers as $supplier ) {
	$row = array();
	foreach ( str_getcsv( $supplier ) as $key => $field )
		$row[ $headers[ $key ] ] = $field;
	$row = array_filter( $row );
	$data[] = $row;
}

if (!!$_FILES && isset($_FILES['textfile']))
{
	$invoices = json_decode( '[' . str_replace("}\r\n{", '},{', str_replace("'", '"', file_get_contents($_FILES['textfile']['tmp_name']))) . ']', true);
	usort($invoices, function($a, $b) {
		$retval = $a['page_id'] <=> $b['page_id'];
		if ($retval == 0) {
			$retval = $a['line_id'] <=> $b['line_id'];
			if ($retval == 0) {
				$retval = $a['word_id'] <=> $b['word_id'];
			}
		}
		return $retval;
	});
}
else
{
	http_response_code(501);
}

foreach ($data as $supplier)
{
	$names = explode(' ', $supplier['SupplierName']);
	foreach ($invoices as $invoice)
	{
		if ($invoice['line_id'] == 4 && $invoice['pos_id'] == 0 && $invoice['word'] == $names[0])
		{
			for ($i = 1; $i < count($names); $i++)
			{
				$key = array_search($invoice['word_id']+$i, array_column($invoices,'word_id'));
				if ($names[$i] == $invoices[$key]['word'])
				{
					$retunValue = $supplier;
				}
				else
				{
					continue 2;
				}
			}
			break 2;
		}
		else
		{
			continue;
		}
	}
}

echo json_encode($retunValue);