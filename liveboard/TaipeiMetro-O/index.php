<?php

$lat = $_GET["lat"];
$lon = $_GET["lon"];

//$lat = 25.046255;
//$lon = 121.517532;
$td = $_GET["time"];
$weekday = date("w",strtotime($td));
$time = date("H:i",strtotime($td));
//BR10;

$StationResult = [];
$StationGeoLocation = [];

$StationGeoLocationStream = fopen("TRTC_Station.json","r");
$tmp = "";
if($StationGeoLocationStream != NULL){
	while (!feof($StationGeoLocationStream)) {
		$tmp .= fgets($StationGeoLocationStream);
	}
}
fclose($StationGeoLocationStream);
$StationGeoLocation = json_decode($tmp);

for($i=0;$i<=sizeOf($StationGeoLocation);$i++){
	//$StationGeoLocation[$i]["distance"] = $StationGeoLocation[$i]["StationPosition"]
	$x = ($StationGeoLocation[$i]->{"StationPosition"}->{"PositionLat"} - $lat)*110.574;
	$y = ($StationGeoLocation[$i]->{"StationPosition"}->{"PositionLon"} - $lon)*111.320;
	$x = pow($x,2);
	$y = pow($y,2);
	$distance = round(sqrt($x + $y),2);
	//echo $distance."\r\n";
	if($distance <= 0.5){
		if(strpos($StationGeoLocation[$i]->{"StationID"},"O") !== false){
			//print_r($StationGeoLocation[$i]);
			$StationID = $StationGeoLocation[$i]->{"StationID"};
			$StationName = $StationGeoLocation[$i]->{"StationName"}->{"Zh_tw"};
			array_push($StationResult,array("StationID" => $StationID,"StationName" => $StationName));
		}
	}
}

for($k=0;$k<sizeOf($StationResult);$k++){
	$StationLiveboardStream = fopen($StationResult[$k]["StationID"].".json","r");
	$tmp = "";
	if($StationLiveboardStream != NULL){
		while (!feof($StationLiveboardStream)) {
			$tmp .= fgets($StationLiveboardStream);
		}
	}
	fclose($StationLiveboardStream);
	$StationLiveboard = json_decode($tmp);
	$tmpArr = [];

	for($l=0;$l<=sizeOf($StationLiveboard->{"Timetables"});$l++){
		for($i=0;$i<=sizeOf($StationLiveboard->{"Timetables"}[$l]->{"Schedule"});$i++){
			if(strpos($StationLiveboard->{"Timetables"}[$l]->{"Schedule"}[$i]->{"Days"},$weekday) !== false){
				for($j=0;$j<=sizeOf($StationLiveboard->{"Timetables"}[$l]->{"Schedule"}[$i]->{"Departures"});$j++){
					$testDate = date("H:i",strtotime($StationLiveboard->{"Timetables"}[$l]->{"Schedule"}[$i]->{"Departures"}[$j]->{"Time"}));
					if($testDate > $time){
						
						switch($StationLiveboard->{"Timetables"}[$l]->{"Direction"}){
							case "往O54蘆洲站、O21迴龍站":
								$direction = 0;
								break;
							case "往O01南勢角站":
								$direction = 1;
								break;
						}	
						array_push($tmpArr,array('StationName' => $StationLiveboard->{"StationName"}, 'Direction' => $direction,'DestinationStationName' => $StationLiveboard->{"Timetables"}[$l]->{"Schedule"}[$i]->{"Departures"}[$j-1]->{"Dst"}, 'Time' => $testDate));
					}
				}
			}
		}
	}
	if($tmpArr !== []){
		$StationResult[$k]["Schedule"] = $tmpArr;
	}
	$tmpArr = [];
}


//print_r($StationResult);
//print_r($StationLiveboard->{"Timetables"}[0]->{"Schedule"});


for($i=0;$i<sizeOf($StationResult);$i++){
	usort($StationResult[$i]["Schedule"], function($a, $b) {
		return $a['Time'] <=> $b['Time'];
	});
}
//print_r($StationResult);
//start to construct the information to Mirror
$result = [];
for($i=0;$i<sizeOf($StationResult);$i++){
	$southboundFlag = 0;
	$northboundFlag = 0;
	//$icn = $StationResult[$i]["StationID"];

	$icn = "./liveboard/TaipeiMetro/O.png";

	$row1 = "";
	$row2 = "";
	$row3 = "";
	$row4 = "";
	$icnrow = "";
	
	$icnrow = $StationResult[$i]["StationName"];

	for($j=0;$j<=sizeOf($StationResult[$i]["Schedule"]);$j++){
		if($StationResult[$i]["Schedule"][$j]["Direction"] == 1 && $southboundFlag == 0){
			if($StationResult[$i]["Schedule"][$j]["DestinationStationName"] !== NULL){
				$southboundFlag = 1;
				$row1 = "往 ".$StationResult[$i]["Schedule"][$j]["DestinationStationName"];
				$row2 = $StationResult[$i]["Schedule"][$j]["Time"];
			}
		}else if($StationResult[$i]["Schedule"][$j]["Direction"] == 0 && $northboundFlag == 0){
			if($StationResult[$i]["Schedule"][$j]["DestinationStationName"] !== NULL){
				$northboundFlag = 1;
				$row3 = "往 ".$StationResult[$i]["Schedule"][$j]["DestinationStationName"];
				$row4 = $StationResult[$i]["Schedule"][$j]["Time"];
			}
		}
	}
	
	array_push($result,array("icn" => $icn,"icnrow" => $icnrow,"row1" => $row1,"row2" => $row2,"row3" => $row3,"row4" => $row4));
}

echo json_encode($result);
?>