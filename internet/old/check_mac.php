<?php
////////////////////////////////////////////////////
////						////
////	ПРАВИЛЬНОЕ ВРЕМЯ НА РОУТЕРЕ!!!!!!	////
//// 	ДОСТУП К ВЕБ ПРИЛОЖЕНИЮ БЕЗ НАТА 	////
////	ЧТОБЫ ВИДЕТЬ ВНУТРЕННИЙ АЙПИ		////
////	НА МАРШРУТИЗАТОРЕ ДОЛЖНО БЫТЬ ЛИЗИНГ 	////
////	НА СТУКИ И КРОН РЕСТАРУЕТ ДХЦП 2 РАЗА 	////
////	В ДЕНЬ../etc/init.d/dnsmasq restart	////
////	В FORWARDe первым должно стотяь какоето	////
////	ПРАВИЛО Например запрещающее доступ в 	////
////	локалку 				////
///////////////////////////////////////////////////
////написать скрипт на стороне роутера который 	////
////проверяет вышло ли время в iptables если 	///
////если вышло или 				////
////////////////////////////////////////////////////

//ПЕРЕМННЫЕ НА СТОРОНЕ РОУТЕРА
$routers = array
	(
		array('ip'=>'192.168.4.4','range'=>'192.168.93','user'=>'root','pass'=>'356386','type'=>'openwrt'),
		array('ip'=>'192.168.241.2','range'=>'192.168.92','user'=>'admin','pass'=>'356386','type'=>'mikrotik')
	);
//ПЕРЕМННЫЙ НА СТОРОНЕ СЕРВЕРА
$addedfiles = "/server/ip/added.txt";
$iptimes = "/server/time/";

function check_lease($ip,$in1)
{
        $ssa = strrpos($in1,$ip);
        return $ssa;
}

function search_mac($ssa,$in1,$type,$ip)
{
	if ($type == "mikrotik")
	{
	preg_match("/(\d+).*$ip\s+(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})/",$in1,$out33);
	return $out33[2];
	}
	if ($type == "openwrt")
	{
	$ip = 1;
	$MAC = substr($in1,$ssa-18,17);
	return $MAC;
	}
}

function put_mac($ip,$MAC,$type)
{
        if ($type == "mikrotik")
        {
		$ss[] = "/ip dhcp-server lease add address=".$ip." mac-address=".$MAC;
		return $ss;
	}
	if ($type == "openwrt")
	{
	$in2 = array("echo \"config host\" >> /etc/config/dhcp", "echo \"option ip '".$ip."'\" >> /etc/config/dhcp","echo \"option mac '".$MAC."'\" >> /etc/config/dhcp");
	return $in2;
	}
}

function del_mac($ip,$type,$in1)
{
	if ($type == "mikrotik")
	{
        preg_match("/(\d+).*$ip\s+(\w{2}:\w{2}:\w{2}:\w{2}:\w{2}:\w{2})/",$in1,$out33);
	$out33 = "/ip dhcp-server lease remove ".$out33[1];
	return $out33;
	}
	if($type == "openwrt")
	{
		$in1 = 1;
		$ss = "sed -i '/^config host/ { :a; N; /\\noption mac/!ba }; /$ip/ d' /etc/config/dhcp";
		return $ss;
	}
}

function put_iptables($ip,$exp,$type)
{
	if ($type == "mikrotik")
        {
	$show = $exp - time();
	$dd = "/ip fire address-list add address=".$ip." timeout=".$show."s list=NoChange";
	echo $dd;
	return $dd;
	}
	if($type == "openwrt")
        {
	$show = date("Y-m-d\TH:i:s",$exp);
	$iptable = "iptables -I FORWARD 3 --source $ip -m time --kerneltz --datestop $show -j ACCEPT";
	$nat = "iptables -t nat -I PREROUTING 1 --source $ip -m time --kerneltz --datestop $show -j ACCEPT";
	return "echo \"".$iptable."\" >> /etc/firewall.user && echo \"".$nat."\" >> /etc/firewall.user";
	}
}
function del_iptables($ip,$type,$in3)
{
        if ($type == "mikrotik")
        {
	preg_match("/.*(\d+).*\s+$ip\s+.*/",$in3,$out33);
	$ss = "/ip fire address-list remove ".$out33[1];
	return $ss;
	}
	if($type == "openwrt")
	{
		$in3 = null;
		$s = "sed -i /".$ip."/d /etc/firewall.user";
		return $s;
	}
}





//ПРЕЛЮДИЯ
$s = scandir($iptimes);
$ss = count($s);
for ($i = 2; $i<$ss; $i++)
{
$a = $s[$i];
$b = file_get_contents($iptimes.$s[$i]);
$b = str_replace("\n","",$b);
//$b = substr($b,0,-1);
$sss[$a] = $b;
}

if ($ss == 2)
{return;}
$added = unserialize(file_get_contents($addedfiles));
$time = time();
//ОСНОВНОЙ ЦИКЛ
foreach ($sss as $ip => $exp_time)
{
echo "\n-------".$exp_time."-------\n";

$aag = strrpos($ip,".");
$fft = substr($ip,0,$aag);
	foreach($routers as $ids=> $route)
	{
		if ($fft == $route['range'])
		{
			$ids1 =$ids;
		}
	}


$rrr = $routers[$ids1];
if($rrr['type']=="openwrt")
{
$cmd1 = "cat /var/dhcp.leases";
$cmd2 = "cat /etc/firewall.user";
$cmd3 = "cat /etc/config/dhcp";
$in3 = "1";
}
if($rrr['type']=="mikrotik")
{
$cmd1 = "/ip dhcp-server lease print";
$cmd2 = "/ip firewall address-list print";
$cmd3 = $cmd1;
$in3 = "1";
}












	if ($exp_time > $time)//если время пока еще не вышло
	{
		if(empty($added) == false && is_array($added) == true)//МАССИВ ИЛИ НЕ МАССИВ BИЛИ ПУСТОЙ МАССИВ???
		{
			if (FALSE == array_key_exists($ip,$added))//если в списке добавленных нету сохраняю в файл.
			{
				a:
				$conn = ssh2_connect($rrr['ip'], 22);
				if (ssh2_auth_password($conn,$rrr['user'],$rrr['pass'])==true)
				{
					echo "Соединеие успешное\n";
				}
				else
				{
					echo "соединение пропало CONTINUE\n";
					continue; //ЕСЛИ СВЯЗИ НЕТУ ПРОПУСТИТЬ!!!!!!!!!
				}
				$stream = ssh2_exec($conn, $cmd1);
//				$errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
//				stream_set_blocking($errorStream, true);
				stream_set_blocking($stream, true);
				$in1 = stream_get_contents($stream);
				$ssa = check_lease($ip,$in1);
				if ($ssa == FALSE)// если мак не найден игнорить
				{
					echo "ВНИМАНИЕ МАК АДРЕСС НЕ НАЙДЕН!!! на ".$ip."\n";
				}
				else
				{
				$MAC = search_mac($ssa,$in1,$rrr['type'],$ip);
				$delmac = del_mac($ip,$rrr['type'],$in1);
				ssh2_exec($conn,$delmac);
				$mac_put = put_mac($ip,$MAC,$rrr['type']);
					foreach($mac_put as $put)
						{
                                		$stream = ssh2_exec($conn,$put);
						}
                                if($rrr['type']=="mikrotik") //ЕСЛИ ОПЕН ВРТ ТО НЕ НУЖНО!!!!
                                {
	                                $stream = ssh2_exec($conn,$cmd2);
                                        stream_set_blocking($stream, true);
                                	$in3 = stream_get_contents($stream);
                                }
                                $in4 = del_iptables($ip,$rrr['type'],$in3);
                                ssh2_exec($conn,$in4);
				$iptable = put_iptables($ip,$exp_time,$rrr['type']);
				ssh2_exec($conn,$iptable);

                                if($rrr['type']=="openwrt")//ЕСЛИ ЭТО ОПЕНВРТ НАДО РЕСТАРТОВАТЬ FW
                                {
                                        $stream = ssh2_exec($conn,"/etc/init.d/firewall restart");
//					$errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
//					stream_set_blocking($errorStream, true);
					stream_set_blocking($stream, true);
					stream_get_contents($stream);
//					stream_get_contents($errorStream);
                                }

				$added2[$ip] = $exp_time;
//				fclose($errorStream);
				fclose($stream);
				}

			}
			else//если в списке добавленных есть
			{
				echo "Время еще не вышло в списке добавленных УЖЕ ЕСТЬ ".$ip."\n";
					if ($exp_time == $added[$ip])
					{
						echo "время НЕ меняли ".$ip." было ".$added[$ip]." стало ".$exp_time."\n";
					}
					else
					{

                                		$conn = ssh2_connect($rrr['ip'], 22);
                		                if (ssh2_auth_password($conn,$rrr['user'],$rrr['pass'])==true)
		                                {
                                        		echo "Соединеие успешное\n";
                                		}
                		                else
		                                {
                        		                echo "соединение пропало CONTINUE\n";
                	                	        continue; //ЕСЛИ СВЯЗИ НЕТУ ПРОПУСТИТЬ!!!!!!!!!
		                                }

						echo "время МЕНЯЛИ ".$ip." было ".$added[$ip]." стало ".$exp_time."\n";
						$added2[$ip] = $exp_time;
						if($rrr['type']=="mikrotik") //ЕСЛИ ОПЕН ВРТ ТО НЕ НУЖНО!!!!
						{
							$stream = ssh2_exec($conn,$cmd2);
	                                        	stream_set_blocking($stream, true);
                                        		$in3 = stream_get_contents($stream);
						}
						$in4 = del_iptables($ip,$rrr['type'],$in3);
						ssh2_exec($conn,$in4);
		                                $iptable = put_iptables($ip,$exp_time,$rrr['type']);
                               			ssh2_exec($conn,$iptable);
		                                if($rrr['type']=="openwrt")//ЕСЛИ ЭТО ОПЕНВРТ НАДО РЕСТАРТОВАТЬ FW
                		                {
                                		        $stream = ssh2_exec($conn,"/etc/init.d/firewall restart");
//		                                        $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
//		                                        stream_set_blocking($errorStream, true);
		                                        stream_set_blocking($stream, true);
		                                        stream_get_contents($stream);
//		                                        stream_get_contents($errorStream);
                		                }
//		                                fclose($errorStream);
		                                fclose($stream);

					}

			}

		}
		else//Создаем новый файл
		{
			goto a;
		}
	}
	else //если время уже вышло удалить все нахрен... из списка и сам файл тоже.
	{

		$conn = ssh2_connect($rrr['ip'], 22);
		if (ssh2_auth_password($conn,$rrr['user'],$rrr['pass'])==true)
		{
			echo "Соединеие успешное\n";
		}
		else
		{
			echo "соединение пропало CONTINUE\n";
			continue; //ЕСЛИ СВЯЗИ НЕТУ ПРОПУСТИТЬ!!!!!!!!!
		}
		echo "время уже вышло удаляю файл и из списка добавленных ".$ip."\n";
		unlink($iptimes.$ip);

		if($rrr['type']=="mikrotik") //ЕСЛИ ОПЕН ВРТ ТО НЕ НУЖНО!!!!
		{
			$stream = ssh2_exec($conn,$cmd2);
			stream_set_blocking($stream, true);
			$in3 = stream_get_contents($stream);
		}
		$in4 = del_iptables($ip,$rrr['type'],$in3);
		ssh2_exec($conn,$in4);

		if($rrr['type']=="openwrt")//ЕСЛИ ЭТО ОПЕНВРТ НАДО РЕСТАРТОВАТЬ FW
		{
			$stream = ssh2_exec($conn,"/etc/init.d/firewall restart");
//			$errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
//			stream_set_blocking($errorStream, true);
			stream_set_blocking($stream, true);
			stream_get_contents($stream);
//			stream_get_contents($errorStream);
		}

		$stream = ssh2_exec($conn,$cmd3);
		stream_set_blocking($stream, true);
		$in7 = stream_get_contents($stream);
		$out = del_mac($ip,$rrr['type'],$in7);
		$stream = ssh2_exec($conn,$out);
                $ipdell[$ip] = $exp_time;
		fclose($stream);

	}

}



if (empty($added2) == FALSE)
{
	echo "Добавляемая переменная существует\n";
	if (is_array($added2) == TRUE)
	{
		echo "Добавляемая переменная массив\n";
		if (empty($added) == TRUE)
		{
			if (empty($ipdell)==false)
			{
				echo "Сохраненная переменная пустая, протсо добавляю, переменная удаления сущестует\n";
				file_put_contents($addedfiles,serialize(array_diff_key($added2,$ipdell)));
			}
			else
			{
				echo "Сохраненная переменная пустая, протсо добавляю\n";
				file_put_contents($addedfiles,serialize($added2));
			}
		}
		else
		{
			echo "Сохраненная переменная не пустая\n";
			if (is_array($added) == TRUE)
			{
				if (empty($ipdell)==false)
	                        {
					echo "Сохраненная переменная массив, добавляю новые элементы, перемнная удалния существует\n";
					$merg = array_merge($added,$added2);
					$differ = array_diff_key($merg,$ipdell);
					file_put_contents($addedfiles,serialize($differ));
				}
				else
				{
					echo "Сохраненная переменная массив, добавляю новые элементы\n";
					file_put_contents($addedfiles,serialize(array_merge($added,$added2)));
				}
			}
			else
			{
				echo "ошибка из сохраненного файла $addedfiles идет перменная НЕ МАССИВ!!!!!! ничего не добавлено\n";
			}
		}
	}
	else
	{
		echo "ОШИБКА!!! добавляемая переменаня не массив\n";
	}

}
else
{
	echo "ничего добавлять не буду добавляемая переменная пустая\n";
	if (empty($ipdell)==false)
	{
		echo "перемнная удалния существует\n";
		//print_r($ipdell);
		//print_r($added);
		$aaa = array_diff_key($added,$ipdell);
		//print_r($aaa);
		file_put_contents($addedfiles,serialize($aaa));


	}
	else
	{
		echo "перемнная удалния отсутвует\n";
	}

}
?>
