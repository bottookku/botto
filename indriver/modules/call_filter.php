<?php
class pointLocation {
    function pointLocation() {
    }
    function pointInPolygon($point, $polygon, $pointOnVertex = true) {
        $vertices = $polygon;
        $intersections = 0;
        $vertices_count = count($vertices);
        for ($i=1; $i < $vertices_count; $i++) {
            $vertex1 = $vertices[$i-1];
            $vertex2 = $vertices[$i];

            if ($point['lat'] > min($vertex1['lat'], $vertex2['lat']) and $point['lat'] <= max($vertex1['lat'], $vertex2['lat']) and $point['long'] <= max($vertex1['long'], $vertex2['long']) and $vertex1['lat'] != $vertex2['lat']) {
                $xinters = ($point['lat'] - $vertex1['lat']) * ($vertex2['long'] - $vertex1['long']) / ($vertex2['lat'] - $vertex1['lat']) + $vertex1['long'];
                if ($vertex1['long'] == $vertex2['long'] || $point['long'] <= $xinters) {
                    $intersections++;
                }
            }

        }
        if ($intersections % 2 != 0) {
            return "inside";
        } else {
            return "outside";
        }
    }

}
require_once "/var/www/indriver/function/db.php";
//взять полигоны
$b = "SELECT * FROM indriver.polygon_current";
$ss = db_reqss($b);
foreach($ss as $ss1)
{
	$a = "SELECT `long`,`lat` FROM `polygon_current` LEFT JOIN `polygon` ON `polygon_current`.`polygon_name_id`=`polygon`.`polygon_name_id` LEFT JOIN `polygon_geo` ON `polygon`.`polygon_geo_id`=`polygon_geo`.`polygon_geo_id` WHERE `polygon_current`.`polygon_name_id`=".$ss1['polygon_name_id'];
	$sss1[$ss1['polygon_name_id']] = db_reqs($a);
}
$req = "SELECT * FROM filtres;";
$ret = db_req($req);
//Максимальное количество итераций основного цикла
$max_itter = 60;
//Отсутсвие десрипшна
$descr = "";
//цена
$price = $ret['price'];
//дистанция
$dist0 = $ret['dist'];
//обновление статуса звонка
$call_status = 55;
include "call_status_update.php";
$postdata = array(
	'phone'         => '+79243684821',
	'token'         => 'c49b8862c1e07f8fd5bf2aa976ca49e9',
	'v'             => '2',
	//'city_id'       => '1',
);

//отправляемые заголовки

$header = array(
	'Connection: Keep-Alive',
	'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.3) Gecko/20100401',
	'Host: indriver.ru'
);

$ch = curl_init();
$itter = 0;
while(true)
{
	$itter++;
	if($itter>=$max_itter)
	{
		echo "off";
		return;
	}
	//echo $itter;
        curl_setopt_array($ch, array(
                CURLOPT_NOBODY => 0,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => "http://178.248.236.45/api/getlastorders",
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => $postdata,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_HEADER => 0,
		CURLOPT_HTTPHEADER => $header,
        ));
	$out = curl_exec($ch);
	$idd = json_decode($out);
	//print_r($idd);
	foreach($idd->response->items as $s => $ss)
	{
		if ($idd->response->items[$s]->description == $descr)
	    {
			if($idd->response->items[$s]->price >= $price)
			{
				$req = "SELECT id_tax FROM current_taxa ORDER BY current_taxa_id DESC LIMIT 5;";
				$id_tax = db_reqs($req);
				foreach($id_tax as $is_id)
				{
					if($is_id['id_tax'] != $idd->response->items[$s]->id)
					{
						$long22 = $idd->response->items[$s]->client->locationlongitude;
						$lat22 = $idd->response->items[$s]->client->locationlatitude;
						$lat2 = $idd->response->items[$s]->fromlatitude;
						$long2 = $idd->response->items[$s]->fromlongitude;
						if($long22 == ""&&$long2 == "")
						{
							continue;
						}
						else
						{
							if($long22=="")
							{
							}
							if($long2=="")
							{
								$lat2 = $lat22;
								$long2 = $long22;
							}
						}
						$point = array('lat'=>$lat2,'long'=>$long2);
						$pointLocation = new pointLocation();
						foreach($sss1 as $dist1 => $ssd1)
						{
							if( $pointLocation->pointInPolygon($point, $ssd1) == "inside")
							{
								$from = 	$idd->response->items[$s]->from;
								$to = 		$idd->response->items[$s]->to;
								$price = 	$idd->response->items[$s]->price;
								$phone =  	$idd->response->items[$s]->client->phone;
								$idd1 =		$idd->response->items[$s]->id;
								$req = "INSERT INTO `indriver`.`current_taxa` (`from`, `to`, `price`, `phone`, `dist`, `id_tax`) VALUES ('$from', '$to', '$price', '$phone', '$dist1', '$idd1');";
								echo $phone."@suka";
								db_req_without_resp($req);
								$call_status = 88;
								include "call_status_update.php";
								curl_close($ch);
								return;
							}
						}
					}
					else
					{
						break 2;
					}
				}
			}
		}
	}
	sleep(1);
}
?>
