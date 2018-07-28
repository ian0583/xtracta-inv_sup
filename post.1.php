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
	http_response_code( 501 );
}

foreach ( $invoices as $invoice )
{
	$firstword = $invoice[ 'word' ];
	$regex = "/" . preg_quote( $firstword, '/' ) . "/i";

	$matches = array_filter($data, function($a) use($regex)  {
		return preg_grep($regex, $a);
	});
	if ( !!$matches )
	{
		$key = array_search($invoice['word_id']+1, array_column($invoices,'word_id'));
		$newword = $firstword . ' ' . $invoices[$key]['word'];
		$regex = "/" . preg_quote( $newword, '/' ) . "/i";
		$matches = array_filter($matches, function($a) use($regex)  {
			return preg_grep($regex, $a);
		});
		if (!!$matches)
		{
			$retunValue = array_values($matches);
			break;
		}
	}
}


echo json_encode( $retunValue );