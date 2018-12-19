<?php

$lat = $_GET["lat"];
$lon = $_GET["lon"];

//$lat = 25.046255;
//$lon = 121.517532;
$td = $_GET["time"];
$weekday = date("w",strtotime($td));
$time = date("H:i",strtotime($td));
//BR10;

switch($weekday){
	case 0:
		$week = "Sunday";
		break;
	case 1:
		$week = "Monday";
		break;
	case 2:
		$week = "Tuesday";
		break;
	case 3:
		$week = "Wednesday";
		break;
	case 4:
		$week = "Thursday";
		break;
	case 5:
		$week = "Friday";
		break;
	case 6:
		$week = "Saturday";
		break;
}

$StationResult = [];
$StationGeoLocation = [];

$StationGeoLocationStream = fopen("Station.json","r");
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
	if($distance <= 1){
			//print_r($StationGeoLocation[$i]);
			$StationID = $StationGeoLocation[$i]->{"StationID"};
			$StationName = $StationGeoLocation[$i]->{"StationName"}->{"Zh_tw"};
			array_push($StationResult,array("StationID" => $StationID,"StationName" => $StationName));
	}
}


//print_r($StationResult);

$StationLiveboardStream = fopen("GeneralTimetable.json","r");
$tmp = "";
if($StationLiveboardStream != NULL){
	while (!feof($StationLiveboardStream)) {
		$tmp .= fgets($StationLiveboardStream);
	}
}
fclose($StationLiveboardStream);
$StationLiveboard = json_decode($tmp);



for($h=0;$h<sizeOf($StationResult);$h++){
	for($i=0;$i<sizeOf($StationLiveboard);$i++){
		if($StationLiveboard[$i]->{"GeneralTimetable"}->{"ServiceDay"}->{$week}){
			for($j=0;$j<sizeOf($StationLiveboard[$i]->{"GeneralTimetable"}->{"StopTimes"});$j++){
				if($StationLiveboard[$i]->{"GeneralTimetable"}->{"StopTimes"}[$j]->{"StationID"} == $StationResult[$h]["StationID"]){
					$StationResult[$h]["Schedule"][$i] = array('TrainNo' => $StationLiveboard[$i]->{"GeneralTimetable"}->{"GeneralTrainInfo"}->{"TrainNo"},'Direction' => $StationLiveboard[$i]->{"GeneralTimetable"}->{"GeneralTrainInfo"}->{"Direction"},'StartingStationName' => $StationLiveboard[$i]->{"GeneralTimetable"}->{"GeneralTrainInfo"}->{"StartingStationName"}->{"Zh_tw"},'EndingStationName' => $StationLiveboard[$i]->{"GeneralTimetable"}->{"GeneralTrainInfo"}->{"EndingStationName"}->{"Zh_tw"},"StopSequence" => $StationLiveboard[$i]->{"GeneralTimetable"}->{"StopTimes"}[$j]->{"StopSequence"},"DepartureTime" => $StationLiveboard[$i]->{"GeneralTimetable"}->{"StopTimes"}[$j]->{"DepartureTime"});
					//seem timetable is not neccessary at now,
					// ,"Timetable" => $StationLiveboard[$i]->{"GeneralTimetable"}->{"StopTimes"}
				}
			}
		}
	}
}

for($i=0;$i<sizeOf($StationResult);$i++){
	usort($StationResult[$i]["Schedule"], function($a, $b) {
		return $a['DepartureTime'] <=> $b['DepartureTime'];
	});
}


//print_r($StationResult);
//start to construct the information to Mirror
$result = [];
for($i=0;$i<sizeOf($StationResult);$i++){
	$southboundFlag = 0;
	$northboundFlag = 0;
	//$icn = $StationResult[$i]["StationID"];

	$icn = "./liveboard/TaiwanHSR/THSR.png";

	$row1 = "";
	$row2 = "";
	$row3 = "";
	$row4 = "";
	$icnrow = "";
	
	$icnrow = $StationResult[$i]["StationName"];

	for($j=0;$j<sizeOf($StationResult[$i]["Schedule"]);$j++){
		if($StationResult[$i]["Schedule"][$j]["Direction"] == 1 && $southboundFlag == 0){
			if($StationResult[$i]["Schedule"][$j]["EndingStationName"] !== NULL){
				if($StationResult[$i]["Schedule"][$j]["StartingStationName"] !== NULL){
				$southboundFlag = 1;
				$row1 = $StationResult[$i]["Schedule"][$j]["StartingStationName"]." 往 ".$StationResult[$i]["Schedule"][$j]["EndingStationName"];
				$row2 = $StationResult[$i]["Schedule"][$j]["DepartureTime"];
				}
			}
		}else if($StationResult[$i]["Schedule"][$j]["Direction"] == 0 && $northboundFlag == 0){
			if($StationResult[$i]["Schedule"][$j]["EndingStationName"] !== NULL){
				if($StationResult[$i]["Schedule"][$j]["StartingStationName"] !== NULL){
					$northboundFlag = 1;
					$row3 = $StationResult[$i]["Schedule"][$j]["StartingStationName"]." 往 ".$StationResult[$i]["Schedule"][$j]["EndingStationName"];
					$row4 = $StationResult[$i]["Schedule"][$j]["DepartureTime"];
				}
			}
		}
	}
	
	array_push($result,array("icn" => $icn,"icnrow" => $icnrow,"row1" => $row1,"row2" => $row2,"row3" => $row3,"row4" => $row4));
}

echo json_encode($result);
?>