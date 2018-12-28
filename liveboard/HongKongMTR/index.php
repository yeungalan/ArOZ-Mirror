<?php
$mtr = new SimpleXMLElement(file_get_contents("http://www.mtr.com.hk/alert/ryg_line_status.xml"));

//print_r($mtr);

$chname["TWL"] = "荃灣綫";
$chname["KTL"] = "觀塘綫";
$chname["ISL"] = "港島綫";
$chname["SIL"] = "南港島綫";
$chname["TKL"] = "將軍澳綫";
$chname["TCL"] = "東涌綫";
$chname["DRL"] = "迪士尼綫";
$chname["AEL"] = "機場快綫";
$chname["EAL"] = "東鐵綫";
$chname["WRL"] = "西鐵綫";
$chname["MOL"] = "馬鞍山綫"; 
$chname["LR"] = "輕鐵綫";


$info["green"] = "列車服務良好";
$info["red"] = "列車服務受阻";
$info["typhoon"] = "列車服務暫停";
$info["grey"] = "列車服務結束";

$bool = false;
foreach($mtr->line as $value){
	$status[(string)$value->line_code] = $info[(string)$value->status];
}


$lat = $_GET["lat"];
$lon = $_GET["lon"];

//$lat = 25.046255;
//$lon = 121.517532;
$td = $_GET["time"];
$weekday = date("w",strtotime($td));
$time = date("H:i",strtotime($td));
$StationResult = [];
$StationGeoLocationStream = fopen("stationLocation.json","r");
$tmp = "";
if($StationGeoLocationStream != NULL){
	while (!feof($StationGeoLocationStream)) {
		$tmp .= fgets($StationGeoLocationStream);
	}
}
fclose($StationGeoLocationStream);
$StationGeoLocation = json_decode($tmp,true);
for($i=0;$i<=sizeOf($StationGeoLocation);$i++){
	$x = ($StationGeoLocation[$i]["lat"] - $lat)*110.574;
	$y = ($StationGeoLocation[$i]["lng"] - $lon)*111.320;
	$x = pow($x,2);
	$y = pow($y,2);
	$distance = round(sqrt($x + $y),2);
	//echo $distance."\r\n";
	if($distance <= 0.5){
			//print_r($StationGeoLocation[$i]);
		$Line = $StationGeoLocation[$i]["line"];
		$StationName = $StationGeoLocation[$i]["zh_name"];
		array_push($StationResult,array("Line" => $Line,"StationName" => $StationName,"Status" => $status[$Line]));
	}
}


$result = [];
for($i=0;$i<sizeOf($StationResult);$i++){
	$icn = "./liveboard/HongKongMTR/".$StationResult[$i]["Line"].".png";
	$icnrow = $StationResult[$i]["StationName"];
	$row1 = "";
	$row2 = "";
	$row3 = $chname[$StationResult[$i]["Line"]];
	$row4 = $StationResult[$i]["Status"];
	array_push($result,array("icn" => $icn,"icnrow" => $icnrow,"row1" => $row1,"row2" => $row2,"row3" => $row3,"row4" => $row4));
}
echo json_encode($result);
?>