<?php
include '../auth.php';
?>
<html>
<head>
<?php
//header("Content-Type: text/plain");
  $bg = array('1.jpeg','2.jpeg','3.jpeg','4.jpeg','5.jpeg','6.jpeg','7.jpeg','8.jpeg','9.jpeg'); // array of filenames

  $i = rand(0, count($bg)-1); // generate random number size of the array
  $selectedBg = "$bg[$i]"; // set variable equal to which random filename was chosen
?>
<title>ArOZ Mirror</title>
<link rel="stylesheet" href="../script/tocas/tocas.css">
<script src="../script/tocas/tocas.js"></script>
<script src="../script/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/weather-icons/2.0.9/css/weather-icons-wind.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/weather-icons/2.0.9/css/weather-icons.min.css">
<style>
  h1 { margin:0 0 10px 0; }
  .wrapper { position: relative; height:200px; width:300px; margin:20px 0; overflow:hidden; }
  .content { position:absolute; bottom:0; width:100%; }
  .content div { padding:10px;}
  
  body{
background: url(img/bg/<?php echo $selectedBg; ?>) no-repeat;
    background-size:     cover;                      /* <------ */
    background-repeat:   no-repeat;
}

#primary {
    max-width: 200px;
    text-decoration: none;
    padding: 10px;
    margin: 1em auto;
    border-radius: 8px;
    color: #007aff;
    -webkit-transition-property: -webkit-transform;
    transition-property: -webkit-transform;
    transition-property: transform;
    transition-property: transform,-webkit-transform;
    -webkit-transition-timing-function: ease-in-out;
    transition-timing-function: ease-in-out;
    -webkit-transition-duration: .2s;
    transition-duration: .2s;
    -webkit-tap-highlight-color: transparent;
	background: rgba(164,164,164,0.5);
    color: #fff;
}
</style>
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">

<!-- style="background-color:black;color:white;" -->
</head>
<body style="color:white;">
<div id="time" align="right" style="position: fixed; top: 10%; right: 5%; width: auto; height: 300px;">
<div id="dayOfWeek" style="font-size: 5vh;height:5vh;"></div>
<div id="CurrentDate" style="font-size: 4vh;height:4vh;"></div>
<div id="CurrentTime" style="font-size: 3vh;height:3vh;"></div>
</div>

<div id="weather" align="left" style="position: fixed; top: 10%; left: 5%; width: auto; height: 500px;">

<div style="font-size: 4vh;height:4vh;" id="country" style="text-shadow: -1px 0 black, 0 1px black, 1px 0 black, 0 -1px black !important;"></div>
<div style="font-size: 2vh;height:2vh;">
   <div class="h1"></div>
   <p id="city" style="font-size: 3vh;">UnKnown</p>
   <br>
   <P><i class="wi wi-night-sleet"  style="font-size:80px" id="weathericon"></i></p>
   <div class="h2" id="temp" style="text-shadow: -1px 0 black, 0 1px black, 1px 0 black, 0 -1px black;">-273&nbsp;°C</div>
   <p id="description" style="text-shadow: -1px 0 black, 0 1px black, 1px 0 black, 0 -1px black;">NULL</p>
   <!-- <br class="clear"> -->
   <p id="details" style="text-shadow: -1px 0 black, 0 1px black, 1px 0 black, 0 -1px black;">NULL</p>
   
   <div id="primary">
		Transport Information Loading...
   </div>
   
</div>
</div>



<div class="wrapper" align="right" style="position: fixed; bottom: 5%; right: 5%">
  <div class="content" id="notification">
  </div>
</div>  


</body>
<script>
$( document ).ready(function() {
    var t = setInterval(updateTime,1000);
	show(22.302242, 114.174052);
	setTimeout(getLocation(),1500);
	
	//new function
	setInterval(function(){updateTPI();}, 5000);
	setInterval(function(){getLocation();},60000);
});

function updateTime(){
	var currentdate = new Date(); 
	$("#dayOfWeek").html(GetDay());
	$("#CurrentTime").html(zeroFill(currentdate.getHours(),2) + ":"+ zeroFill(currentdate.getMinutes(),2) + ":"  + zeroFill(currentdate.getSeconds(),2));
	//$("#CurrentDate").html(currentdate.getDate() + "/" + (currentdate.getMonth()+1) + "/" + currentdate.getFullYear());
	$("#CurrentDate").html(GetMonthName() + " " + currentdate.getDate() +", " + currentdate.getFullYear());
}

function GetDay(){
	var d = new Date();
	var weekday = new Array(7);
	weekday[0] =  "Sunday";
	weekday[1] = "Monday";
	weekday[2] = "Tuesday";
	weekday[3] = "Wednesday";
	weekday[4] = "Thursday";
	weekday[5] = "Friday";
	weekday[6] = "Saturday";

	var n = weekday[d.getDay()];
	return n;
}

function GetMonthName(){
	var monthNames = ["January", "February", "March", "April", "May", "June","July", "August", "September", "October", "November", "December"];
	var d = new Date();
	return(monthNames[d.getMonth()]);
}
function zeroFill( number, width )
{
  width -= number.toString().length;
  if ( width > 0 )
  {
    return new Array( width + (/\./.test( number ) ? 2 : 1) ).join( '0' ) + number;
  }
  return number + ""; // always return a string
}
</script>

<script>
var TPI_SourceURL = ["TaipeiMetro/index.php","TaipeiMetro-O/index.php","TaoyuanMetro/index.php","TaiwanHSR/index.php","TaiwanRailway/index.php"];
var TransportInformationArray = [];
var counterforTPI = 0;

function getLocation() {
    if (navigator.geolocation) {
		
			navigator.geolocation.getCurrentPosition(showPosition);
		
    } else { 
        console.log("Geolocation is not supported by this browser.");}
    }
function showPosition(position) {
    console.log("Latitude: " + position.coords.latitude + "Longitude: " + position.coords.longitude);
	show(position.coords.latitude , position.coords.longitude);
	
	//This part for transport information
	TransportInformationArray = [];
	counterforTPI = 0;
	for(var i=0;i<TPI_SourceURL.length;i++){
		fetch(position.coords.latitude , position.coords.longitude,new Date().getFullYear() + "/" + (new Date().getMonth() + 1) + "/" + new Date().getDate() + " " + new Date().getHours() + ":" + new Date().getMinutes(),TPI_SourceURL[i]);
	}
}
function show(rr , xx){
var myObj, myJSON, text, obj;
 $.getScript("https://query.yahooapis.com/v1/public/yql?q=select * from weather.forecast where woeid in (SELECT woeid FROM geo.places WHERE text='("+ rr + ',' + xx +")')and%20u%3D%22c%22&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=yqlhandler&language=en-us");
}
 var yqlhandler = function(data) {
    var location = data.query.results.channel.location;
	var forecast = data.query.results.channel.item;
   console.log(location.country + "," + location.region + "," + location.city);
   $( "#country" ).text(location.country);
   $( "#city" ).text(location.city);
   $( "#weathericon").attr('class',"wi " + weather_icon[forecast.condition.code]);
   $( "#temp" ).text(forecast.condition.temp + " &#8451;");
   $( "#description" ).text(forecast.condition.text);
   $( "#details" ).html('Forecast: ' + forecast.forecast[0].high + ' / ' + forecast.forecast[0].low + '&nbsp;°C<br>Wind: ' + data.query.results.channel.wind.speed + ' km/h <span class="comp sa20" ><i class="wi wi-wind towards-' + data.query.results.channel.wind.direction + '-deg"></i></span> from ' + data.query.results.channel.wind.direction + 'degree');
  };

var template = '<div class="ts grid" id="liveboard"><div class="two column row"><div class="column"><img class="ts fluid image" src="%ICN%">%ICNROW%</div><div class="column"><div class="ts grid"><div class="sixteen wide column">%ROW1%<br>%ROW2%</div><div class="sixteen wide column">%ROW3%<br>%ROW4%</div></div></div></div></div>';

function fetch(lat,lon,time,url){
	$.getJSON( "liveboard/" +  url + "?lat=" + lat + "&lon=" + lon + "&time=" + time, function( json ) {
		TransportInformationArray = TransportInformationArray.concat(json);
	});	
}

function updateTPI(){
	//$("#liveboard").animate({width:'toggle'},350);
	//setTimeout(function(){$("#liveboard").animate({width:'toggle'},350);},1000);
	if(counterforTPI <= TransportInformationArray.length - 1){
		var tmp = template.replace("%ICN%",TransportInformationArray[counterforTPI].icn).replace("%ICNROW%",TransportInformationArray[counterforTPI].icnrow).replace("%ROW1%",TransportInformationArray[counterforTPI].row1).replace("%ROW2%",TransportInformationArray[counterforTPI].row2).replace("%ROW3%",TransportInformationArray[counterforTPI].row3).replace("%ROW4%",TransportInformationArray[counterforTPI].row4);
		if($("#primary").html() !== tmp){
			$("#primary").html(tmp);
		}
		
		counterforTPI += 1;
	}else{
		counterforTPI = 0;
	}
	//console.log(counterforTPI);
}
</script>

<script>
  //thanks for https://erikflowers.github.io/weather-icons/api-list.html
  var weather_icon = {
			"1": "wi-hurricane",
			"2": "wi-tornado",
			"3": "wi-storm-showers",
			"4": "wi-storm-showers",
			"5": "wi-snow",
			"6": "wi-snow",
			"7": "wi-snow",
			"8": "wi-rain-mix",
			"9": "wi-rain-mix",
			"10": "wi-rain",
			"11": "wi-rain",
			"12": "wi-rain",
			"13": "wi-snow",
			"14": "wi-snow",
			"15": "wi-sandstorm",
			"16": "wi-snow",
			"17": "wi-meteor",
			"18": "wi-snow",
			"19": "wi-smog",
			"20": "wi-smog",
			"21": "wi-smog",
			"22": "wi-smog",
			"23": "wi-strong-wind",
			"24": "wi-windy",
			"25": "wi-snowflake-cold",
			"26": "wi-cloudy",
			"27": "wi-night-alt-cloudy",
			"28": "wi-day-cloudy",
			"29": "wi-night-alt-cloudy",
			"30": "wi-day-cloudy",
			"31": "wi-night-clear",
			"32": "wi-day-sunny",
			"33": "wi-night-clear",
			"34": "wi-day-sunny",
			"35": "wi-meteor",
			"36": "wi-hot",
			"37": "wi-sandstorm",
			"38": "wi-storm-showers",
			"39": "wi-storm-showers",
			"40": "wi-storm-showers",
			"41": "wi-snow",
			"42": "wi-snow",
			"43": "wi-snow",
			"44": "wi-snow",
			"45": "wi-storm-showers",
			"46": "wi-snow",
			"47": "wi-storm-showers"
		};
</script>
</html>
