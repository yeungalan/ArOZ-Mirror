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
		//print_r($StationGeoLocation[$i]);
		$StationID = $StationGeoLocation[$i]->{"StationID"};
		$StationName = $StationGeoLocation[$i]->{"StationName"}->{"Zh_tw"};
		
		if(strpos($StationID,"O") === false){
			//because of PTX database dont have Line-O, so add a statement to prevent Line-O
			array_push($StationResult,array("StationID" => $StationID,"StationName" => $StationName));
		}
	}
}

$StationLiveboardStream = fopen("TRTC.json","r");
$tmp = "";
if($StationLiveboardStream != NULL){
	while (!feof($StationLiveboardStream)) {
		$tmp .= fgets($StationLiveboardStream);
	}
}
fclose($StationLiveboardStream);
$StationLiveboard = json_decode($tmp);
$tmpArr = [];
for($j=0;$j<=sizeOf($StationResult);$j++){
	for($i=0;$i<=sizeOf($StationLiveboard);$i++){
		if($StationLiveboard[$i]->{"StationID"} == $StationResult[$j]["StationID"]){
			if($StationLiveboard[$i]->{"ServiceDays"}->{$week} == 1){
				for($k=0;$k<=sizeOf($StationLiveboard[$i]->{'Timetables'});$k++){
					$testDate = date("H:i",strtotime($StationLiveboard[$i]->{'Timetables'}[$k]->{'ArrivalTime'}));
					if($k -1 < 0){
						$itm = $k;
					}else{
						$itm = $k-1;
					}
					$prevDate = date("H:i",strtotime($StationLiveboard[$i]->{'Timetables'}[$itm]->{'ArrivalTime'}));
					if($testDate > $time && $prevDate <= $time){
						array_push($tmpArr,array('RouteID' => $StationLiveboard[$i]->{'RouteID'}, 'StationID' => $StationLiveboard[$i]->{'StationID'},'StationName' => $StationLiveboard[$i]->{"StationName"}->{"Zh_tw"}, 'Direction' => $StationLiveboard[$i]->{'Direction'},
						 'DestinationStaionID' => $StationLiveboard[$i]->{'DestinationStaionID'},'DestinationStationName' => $StationLiveboard[$i]->{"DestinationStationName"}->{"Zh_tw"}, 'Time' => $testDate));
					}
				}
			}
		}
		if($tmpArr !== []){
			$StationResult[$j]["Schedule"] = $tmpArr;
		}
	}
	$tmpArr = [];
}
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
	if(strpos($StationResult[$i]["StationID"],"BR") !== false){
			$icn = "./liveboard/TaipeiMetro/BR.png";
	}else if(strpos($StationResult[$i]["StationID"],"BL") !== false){
			$icn = "./liveboard/TaipeiMetro/BL.png";
	}else if(strpos($StationResult[$i]["StationID"],"G") !== false){
			$icn = "./liveboard/TaipeiMetro/G.png";
	}else if(strpos($StationResult[$i]["StationID"],"O") !== false){
			$icn = "./liveboard/TaipeiMetro/O.png";
	}else if(strpos($StationResult[$i]["StationID"],"R") !== false){
			$icn = "./liveboard/TaipeiMetro/R.png";		
	}
	
	$row1 = "";
	$row2 = "";
	$row3 = "";
	$row4 = "";
	$icnrow = "";
	
	$icnrow = $StationResult[$i]["StationName"];
	if(strpos($StationResult[$i]["StationID"],"BR") !== false){
		$row1 = "文湖線";
		$row2 = "無時刻表";
		$row3 = "";
		$row4 = "";
	}else{
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
	}
	array_push($result,array("icn" => $icn,"icnrow" => $icnrow,"row1" => $row1,"row2" => $row2,"row3" => $row3,"row4" => $row4));
}

echo json_encode($result);
?>