<?php
$day = $_GET["day"];
$day_start_time = $_GET["StartTime"];
$day_end_time = $_GET["EndTime"];
@ $db=new mysqli('your_host','user_name','your_password','database_name');
if (mysqli_connect_errno()) {
        echo 'Error: Could not connect to database.Please try again later.';
        exit;
}
	$query="SELECT * FROM `test` WHERE `date`='$day' and `start` > '$day_start_time' and `start` < '$day_end_time'";
    $result=mysqli_query($db,$query);
    $num_results=mysqli_num_rows($result);
    echo '[';
    $speaker_arr = array();
    $lat_arr = array();
    $lon_arr = array();
    $MFCC_arr = array();
    $gender_arr = array();
    if($num_results == 0) {
       $arr= array('speaker_count'=>'0','latitude'=>'-1','longitude'=>'-1');
        echo json_encode ($arr);
    }
    if($num_results > 0){
        for( $i = 0; $i < $num_results; $i ++){
            $row = mysqli_fetch_assoc($result);
            $speaker_arr[$i] = stripcslashes($row['count']);
            $lat_arr[$i] = stripcslashes($row['latitude']);
            $lon_arr[$i] =stripcslashes($row['longitude']);
            $MFCC_arr[$i] = $row['MFCC'];
            $gender_arr[$i] = $row['gender']; 
        }
        for( $k = 0; $k < sizeof($speaker_arr); $k++){
            if($num_results > 1){
                for($l = $k+1; $l < sizeof($speaker_arr); $l++){
                    $mfcc_arr1 = explode('],[', $MFCC_arr[$k]);
                    $mfcc_arr2 = explode('],[', $MFCC_arr[$l]);
                    $gender_arr1 = explode(',', substr($gender_arr[$k], 1,-1));
                    $gender_arr2 = explode(',', substr($gender_arr[$l], 1,-1));
                    for($m = 0; $m < sizeof($mfcc_arr1); $m++){
                        for($n = 0; $n <sizeof($mfcc_arr2); $n++){
                            $deg = cosine($mfcc_arr1[$m], $mfcc_arr2[$n]);
                            if($gender_arr1[$m] == $gender_arr2[$n] && $deg < 10){
                                $mergin = (int)$speaker_arr[$k] - 1;
                                $speaker_arr[$k] = $mergin;
                                break 2;
                            }
                        }
                    }
                }
            }
        }
    }
    for($i = 0; $i < sizeof($speaker_arr); $i++){
         if((int)$speaker_arr[$i] > 0){
                $arr= array('speaker_count'=>$speaker_arr[$i],'latitude'=>$lat_arr[$i],'longitude'=>$lon_arr[$i]);
                if($i < sizeof($speaker_arr) -1)
                    echo json_encode ($arr).',';
                if( $i == sizeof($speaker_arr)-1)
                    echo json_encode ($arr);
        }
    }
    mysqli_free_result($result);
    mysqli_close($db);
    echo ']';

function cosine($mfcc1, $mfcc2){
    $mfcc1 = substr($mfcc1, 1,-1);
    // echo $mfcc1;
    $mfcc2 = substr($mfcc2, 1,-1);
    $mfcc_a = explode(',', $mfcc1);
    $mfcc_b = explode(',', $mfcc2);
    $dotProduct = 0;
    $Norm1 = 0;
    $Norm2 = 0;
    $deg = 0;
    for($i = 0; $i < sizeof($mfcc_a); $i++){
        $MFCC1 = (double)$mfcc_a[$i];
        $MFCC2 = (double)$mfcc_b[$i];
        $dotProduct = $MFCC1 * $MFCC2;
        $Norm1 = $MFCC1 * $MFCC1;
        $Norm2 = $MFCC2 * $MFCC2;
    }
    $deg = acos($dotProduct / (sqrt($Norm1) * sqrt($Norm2)));
    return $deg;
}
?>