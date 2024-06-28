<?php

$normalizations = [
	'Distance'        => ['new' => 'Distance (normalized)'],
	'Observer Height' => ['new' => 'Observer Height (normalized)'],
	'Target Height'   => ['new' => 'Target Height (normalized)'],
	'Globe Predicted Horizon Distance'  => ['new' => 'Globe Predicted Horizon Distance (normalized)'],
	'Globe Predicted Hidden by Horizon' => ['new' => 'Globe Predicted Hidden by Horizon (normalized)'],
];

$calculations = [
	[
		'new' => 'Verified Curve Drop (linear)',
		'func' => function ($headers,$row) {
			$index1 = array_search('Distance (normalized)',$headers);
			$index2 = array_Search('Observer Height (normalized)',$headers);
			if (!is_numeric($row[$index1]) || !is_numeric($row[$index2])) {
				return null;
			}
			$dist = (float)$row[$index1];
			$height = (float)$row[$index2];
			//formula from Fig. 3 of this document: https://drive.google.com/file/d/1z9w0Rj5POfGCodijzQI0vNbPaH57MfMq/view?usp=sharing
			$c = 40074275.0;
			$r = 6371393.0;
			$theta = $dist * 2 * 3.14159265358979323 / $c; //radians
			$x = acos( $r / ($height + $r) ); //radians
			$y = $theta - $x;
			$hidden = 0.0;
			if ($y != 0.0) {
				$hidden = ( $r / cos($y) ) - $r;
				if ($y < 0.0) {
					$hidden = -$hidden;
				}
			}
			return $hidden;
		},
	],
	[
		'new' => 'Verified Tangent Length',
		'func' => function ($headers,$row) {
			$index2 = array_Search('Observer Height (normalized)',$headers);
			if (!is_numeric($row[$index2])) {
				return null;
			}
			$height = (float)$row[$index2];
			if ($height <= 0.0) {
				return null;
			}
			//formula from page 1 of this document: https://drive.google.com/file/d/1Zh2RxAmfq6ods3s3x0vVqZv-DWJRsTQu/view?usp=sharing
			$r = 6371393.0;
			return sqrt( ($r + $height) ** 2 - $r**2 );
		},
	],
	[
		'new' => 'Flerf Error for Drop (raw)',
		'func' => function ($headers,$row) {
			$index1 = array_search('Globe Predicted Hidden by Horizon (normalized)',$headers);
			$index2 = array_Search('Verified Curve Drop (linear)',$headers);
			if (!is_numeric($row[$index1]) || !is_numeric($row[$index2])) {
				return null;
			}
			$flerf = (float)$row[$index1];
			$real = (float)$row[$index2];
			return $flerf - $real;
		},
	],
	[
		'new' => 'Flerf Error for Tangent (raw)',
		'func' => function ($headers,$row) {
			$index1 = array_search('Globe Predicted Horizon Distance (normalized)',$headers);
			$index2 = array_Search('Verified Tangent Length',$headers);
			if (!is_numeric($row[$index1]) || !is_numeric($row[$index2])) {
				return null;
			}
			$flerf = (float)$row[$index1];
			$real = (float)$row[$index2];
			return $real - $flerf;
		},
	],
	[
		'new' => 'Flerf Error for Drop (percent)',
		'func' => function ($headers,$row) {
			$index1 = array_search('Flerf Error for Drop (raw)',$headers);
			$index2 = array_Search('Verified Curve Drop (linear)',$headers);
			if (!is_numeric($row[$index1]) || !is_numeric($row[$index2])) {
				return null;
			}
			$error = (float)$row[$index1];
			$real = (float)$row[$index2];
			if ($real == 0.0) {
				return null;
			}
			return number_format(100.0 * $error / $real, 2);
		},
	],
	[
		'new' => 'Flerf Error for Tangent (percent)',
		'func' => function ($headers,$row) {
			$index1 = array_search('Flerf Error for Tangent (raw)',$headers);
			$index2 = array_Search('Verified Tangent Length',$headers);
			if (!is_numeric($row[$index1]) || !is_numeric($row[$index2])) {
				return null;
			}
			$error = (float)$row[$index1];
			$real = (float)$row[$index2];
			if ($real == 0.0) {
				return null;
			}
			return number_format(100.0 * $error / $real, 2);
		},
	],
	[
		'new' => 'Y Anomaly (linear)',
		'func' => function ($headers,$row) {
			$index1 = array_search('Target Height (normalized)',$headers);
			$index2 = array_Search('Verified Curve Drop (linear)',$headers);
			if (!is_numeric($row[$index1]) || !is_numeric($row[$index2])) {
				return null;
			}
			$target = (float)$row[$index1];
			$hidden = (float)$row[$index2];
			return ($hidden - $target);
		},
	],
	[
		'new' => 'Z Anomaly (linear)',
		'func' => function ($headers,$row) {
			$index1 = array_search('Distance (normalized)',$headers);
			$index2 = array_Search('Verified Tangent Length',$headers);
			if (!is_numeric($row[$index1]) || !is_numeric($row[$index2])) {
				return null;
			}
			$target = (float)$row[$index1];
			$horizon = (float)$row[$index2];
			return ($target - $horizon);
		},
	],
	[
		'new' => 'Y Anomaly (angular)',
		'func' => function ($headers,$row) {
			$index1 = array_search('Y Anomaly (linear)',$headers);
			$index2 = array_Search('Distance (normalized)',$headers);
			if (!is_numeric($row[$index1]) || !is_numeric($row[$index2])) {
				return null;
			}
			$height = (float)$row[$index1];
			$tangent = (float)$row[$index2];
			return abs(rad2deg(atan($height/$tangent)));
		},
	],
	[
		'new' => 'Z Anomaly (angular)',
		'func' => function ($headers,$row) {
			$index1 = array_search('Distance (normalized)',$headers);
			$index2 = array_Search('Observer Height (normalized)',$headers);
			if (!is_numeric($row[$index1]) || !is_numeric($row[$index2])) {
				return null;
			}
			$dist = (float)$row[$index1];
			$height = (float)$row[$index2];
			$c = 40074275.0;
			$r = 6371393.0;
			$theta = $dist * 2 * 3.14159265358979323 / $c; //radians
			$x = acos( $r / ($height + $r) ); //radians
			$a = cos($theta)*$r; //meters
			$b = ($r+$height)-$a; //meters
			$m = sqrt($r**2-$a**2); //meters
			$y = atan($m/$b); //radians
			return abs(90 - abs(rad2deg($x)) - abs(rad2deg($y)) );
		},
	],
];

// we are converting everything to meters
$units = [
	'miles' => 1609.34,
	'feet' => 0.3048,
	'foot' => 0.3048,
	'kilometres' => 1000.0,
	'inches' => 0.0254,
	'inch' => 0.0254,
	'centimetres' => 0.01,
	'millimetres' => 0.001,
	'metres' => 1.0,
];

$file = fopen("too_far-master.csv","r");
$headers = fgetcsv($file,null,",","\"");

$next_index = count($headers);
foreach ($headers as $i => $column) {
	if (isset($normalizations[$column])) {
		$normalizations[$column]['old_index'] = $i;
		$normalizations[$column]['new_index'] = $next_index++;
	}
}
foreach ($normalizations as $normalization) {
	$headers[$normalization['new_index']] = $normalization['new'];
}
foreach ($calculations as &$calculation) {
	$calculation['new_index'] = $next_index++;
	$headers[$calculation['new_index']] = $calculation['new'];
	unset($calculation);
}

$rows = [];
$index = 0;
while (($row = fgetcsv($file,null,",","\"")) !== false) {
	$rows[$index] = $row;
	foreach ($normalizations as $column => $normalization) {
		$str = explode("(",$row[$normalization['old_index']])[0];
		foreach (array_keys($units) as $unitCompare) {
			if (str_contains(strtolower($str),$unitCompare)) {
				$unit = $unitCompare;
				break;
			}
		}
		foreach (explode(" ",str_replace(",","",$str)) as $word) {
			if (is_numeric($word)) {
				$num = (float)$word;
				break;
			}
		}
		if (isset($unit) && isset($num)) {
			$rows[$index][$normalization['new_index']] = $num * $units[$unit];
		} else {
			$rows[$index][$normalization['new_index']] = "";
		}
		unset($unit);
		unset($num);
	}
	foreach ($calculations as $i => $calculation) {
		$rows[$index][$calculation['new_index']] = $calculation['func']($headers,$rows[$index]);
	}
	$index++;
}

$output = "";
foreach (array_merge([$headers],$rows) as $row) {
	foreach ($row as $i => $column) {
		$output .= "\"" . $column . "\"";
		if ($i < count($row)-1) {
			$output .= ",";
		} else {
			$output .= "\n";
		}
	}
}
file_put_contents("too_far-processed.csv",$output);

