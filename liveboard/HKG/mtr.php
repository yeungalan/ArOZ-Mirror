<?php
$mtr = new SimpleXMLElement(file_get_contents("http://www.mtr.com.hk/alert/ryg_line_status.xml"));

//print_r($mtr);

$arr["TWL"] = "荃灣綫";
$arr["KTL"] = "觀塘綫";
$arr["ISL"] = "港島綫";
$arr["SIL"] = "南港島綫";
$arr["TKL"] = "將軍澳綫";
$arr["TCL"] = "東涌綫";
$arr["DRL"] = "迪士尼綫";
$arr["AEL"] = "機場快綫";
$arr["EAL"] = "東鐵綫";
$arr["WRL"] = "西鐵綫";
$arr["MOL"] = "馬鞍山綫"; 
$arr["LR"] = "輕鐵綫";

$info["green"] = "列車服務良好";
$info["red"] = "列車服務受阻";
$info["typhoon"] = "列車服務暫停";
$info["grey"] = "列車服務結束";

$bool = false;
foreach($mtr->line as $value){
	if((string)$value->status !== "green"){
		//echo $arr[(string)$value->line_code]." ".$info[(string)$value->status]."。";
		$bool = true;
	}
		$result[(string)$value->status] = $result[(string)$value->status].$arr[(string)$value->line_code]."，";
	
}

if($bool == false){
	echo "MTR Good Service.";
}else{
	echo "MTR Service Delay/Disruption.";
}
?>