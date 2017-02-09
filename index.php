<?php
$date = $_POST["date"];
$start = $_POST["start"];
$end = $_POST["end"];
$speaker_count=$_POST["speaker_count"];
$latitude = $_POST["latitude"];
$longitude =$_POST["longitude"];
$MFCC = $_POST["MFCC"];
$gender = $_POST["gender"];

$date = date('Ymd',strtotime($date));
$start = date('His',strtotime($start));
$end = date('His',strtotime($end));

define('EARTHRADIUS',6370996.81);
    
@ $db=new mysqli('your_host','user_name','your_password','database_name');
if (mysqli_connect_errno()) {
        echo 'Error: Could not connect to database.Please try again later.';
        exit;
	}

$start_before = date('His',strtotime("-1 hour"));
 


	$select = "SELECT * FROM `test` WHERE `date`='$date' and `start` >= '$start_before' and `start` <= '$start'";
	$select_data = mysqli_query($db,$select);
	$data_num = mysqli_num_rows($select_data);
	if($data_num > 0){ 
		$lat_arr = array();
		$lon_arr = array();
		$time_arr = array();
		for($i = 0; $i < $data_num; $i++){ 
			$row = mysqli_fetch_assoc($select_data);
			$lat_arr[$i] = stripcslashes($row['latitude']);
			$lon_arr[$i] = stripcslashes($row['longitude']);
			$time_arr[$i] = stripcslashes($row['start']);
			$time_arr[$i] = date('His',strtotime($time_arr[$i]));
            //echo $time_arr[$i];
		}
		mysqli_free_result($select_data);
		$lat_arr[$data_num] = $latitude;
		$lon_arr[$data_num] = $longitude;
		$time_arr[$data_num] = $start;
        for($j = 0; $j < sizeof($lat_arr) - 1; $j++){ 
        	for($k = $j + 1; $k < sizeof($lat_arr); $k++){ 
        		$distance = getDistance($lat_arr[$j],$lon_arr[$j],$lat_arr[$k],$lon_arr[$k]);
        		echo "distance:".$distance;
        		if($distance <= 5){
                        
        			$delete = "DELETE FROM `test` WHERE `date`='$date' and `latitude` = '$lat_arr[$j]' and `longitude` = '$lon_arr[$j]' and `start` = '$time_arr[$j]'"; 
        			$delete_result = mysqli_query($db,$delete);
        			mysqli_free_result($delete_result);    

        		}			
        		
        	}
        }

	}



$query="INSERT INTO `test` VALUES ($date,$start,$end,$speaker_count,$latitude,$longitude,'$MFCC','$gender')";
$result=mysqli_query($db,$query);
mysqli_free_result($result);
mysqli_close($db);


	function getDistance($lat1, $lng1, $lat2, $lng2)
	 {
	 
	     /*
	       Convert these degrees to radians
	       to work with the formula
	     */
	 
	     $lat1 = ($lat1 * pi() ) / 180;
	     $lng1 = ($lng1 * pi() ) / 180;
	 
	     $lat2 = ($lat2 * pi() ) / 180;
	     $lng2 = ($lng2 * pi() ) / 180;
	 
	 
	 
	     $calcLongitude = $lng2 - $lng1;
	     $calcLatitude = $lat2 - $lat1;
	     $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);  
	     $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
	     $calculatedDistance = EARTHRADIUS * $stepTwo;
	 
	     return round($calculatedDistance);
	 }

?>