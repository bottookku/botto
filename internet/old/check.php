<?php
$ss = explode("_",$_COOKIE['id']);
$sss = mysql_connect("localhost", "root", "356386") or
die("Ошибка соединения: " . mysql_error());
mysql_set_charset('utf8',$sss);
mysql_select_db("mydb");
$result2 = mysql_query("SELECT user,pass FROM mydb.user WHERE user=\"".$ss[0]."\"");
while ($row1[]=mysql_fetch_array($result2)) {
}
if($ss[0]==$row1[0][0]&&$ss[1]==$row1[0][1]&&$row1[0][0]!==null)
//ЕСЛИ ПАРОЛЬ И ЛОГИН ИЗ КУКИ ВЕРНЫ...
{
   if($_POST[captcha]===null&&$_POST[idd]===null)
   {
	//ИЩУ МАК...
	$client_ip_address = $_SERVER[REMOTE_ADDR];
	$remote_ip = preg_replace("/\d+$/", "254", $client_ip_address);
	$remote_ip2 = mysql_query("SELECT brand_id,router_pass,router_login FROM mydb.routers WHERE pptp_client_ip='$remote_ip'");
	$remote_ip3 = mysql_fetch_assoc($remote_ip2);
	$brand_id = $remote_ip3[brand_id];
	$router_login = $remote_ip3[router_login];
	$router_pass = $remote_ip3[router_pass];
	if(!is_null($brand_id))
	{
		//Если IP внутрисетевое
		$check_mac0 = "SELECT check_mac FROM mydb.router_brand_list WHERE brand_id=$brand_id";
		$check_mac = mysql_query($check_mac0);
		$check_mac1 = mysql_fetch_assoc($check_mac);
		$check_mac_cmd = $check_mac1[check_mac];
		/////////коннект с роутером/////////
		$conn = ssh2_connect($remote_ip, 22);
		if (ssh2_auth_password($conn,$router_login,$router_pass)==true)
		{
			//Соединеие успешное
		}
		else
		{
			echo "соединение пропало CONTINUE\n";
			continue; //ЕСЛИ СВЯЗИ НЕТУ ПРОПУСТИТЬ!!!!!!!!!
		}
		//echo $check_mac_cmd;
		$cmd1 = str_replace('[ip]',$client_ip_address,$check_mac_cmd);
		$stream = ssh2_exec($conn, $cmd1);
		// $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
		// stream_set_blocking($errorStream, true);
		stream_set_blocking($stream, true);
		$in1 = stream_get_contents($stream);
		//Закрть соединение с роутером
		fclose($stream);
		ssh2_exec($conn,'exit');
		unset($conn);
		// Ищет мак адресс с больишими или мальеньики буквами, разделенный пробелом или : или тире
		// в начале или в конце текста или в серелине текста разделенный от остальных слов пробелом или
		// табуляцией....
		$reg_ex_find_mac = '/(^|\s|\t)(([0-9a-fA-F]{2}[:-\s]){5}[0-9a-fA-F]{2}){1}($|\s|\t)/';
		$if_mac = preg_match($reg_ex_find_mac,$in1,$mac_address_client);
		$mac_address_client = $mac_address_client[2];
		if($if_mac == 1)
		{
			//Все в норме мак есть в спсике лизига
		}
		else
		{
			echo "Мак адреса нет";
			return;
		}
		////////конец коннекта с роутером///////////
	}
	else
	{
		echo "<br>вы не в интрасети!!!<br>";
		return;
		/////////////////////////////////////////////
		////				     	 ////
		////  ТАКОГО IP НЕт в списках роутеров!  ////
		////				     	 ////
		/////////////////////////////////////////////
	}
   }
	//ищу мак конец..
	if($_POST['num']=="")
	{$_POST['num']=1;}
	if($_POST['captcha']=="")
	{
		$KEY = 0;
		goto a;
	}
	if($_POST[captcha]!==null&&$_POST[idd]!==null&&$_POST[mac]!="")
	//если капчу ввели...
	{
		$mac_address_client = $_POST[mac];
		//echo $_POST[mac]."\n";
		//echo $_POST[num]."\n";
		//echo $_POST[idd]."\n";
		$sss = mysql_connect("localhost", "root", "356386") or
		die("Ошибка соединения: " . mysql_error());
		mysql_set_charset('utf8',$sss);
		mysql_select_db("mydb");
		$result2 = mysql_query("SELECT captcha FROM mydb.rekl_page WHERE rekl_id=\"".$_POST[idd]."\" AND num=\"".$_POST[num]."\"");
		while ($row2[]=mysql_fetch_array($result2)){}
		if($row2[0][captcha] == $_POST[captcha])
		//если капча верна
        	{
			//Находим time x
			$time_n = mysql_query("SELECT time_x,time_y FROM mydb.time_interval");
			$time_n = mysql_fetch_assoc($time_n);
			$time_x = $time_n[time_x];
			$time_y = $time_n[time_y];
			//Выявить USER_ID $x[user_id]
			$user_id10 = mysql_query("SELECT user_id FROM mydb.user WHERE user=\"".$ss[0]."\"");
			$user_id10 = mysql_fetch_assoc($user_id10);
			$user_id10 = $user_id10[user_id];
			//ЕСЛИ Номер 1 то проверить последнюю номер 3 оно должно быть дальше X time
			if($_POST[num]==1)
			{
				//выводим * num=3 от user_id за промежуток X с лимитом вывода 1
				//с сортировкой по убыванию времени
				$gh1 = "SELECT * FROM mydb.answer_users WHERE num=3 AND ((user_id=$user_id10 OR mac='$mac_address_client') OR (user_id=$user_id10 AND mac='$mac_address_client')) AND TIMESTAMPDIFF (second,time_stamp,current_timestamp) <= TIME_TO_SEC('$time_x') ORDER BY time_stamp DESC limit 1";
				//echo $gh1."\n";
				$ans_id = mysql_query($gh1);
				$ans_id = mysql_fetch_assoc($ans_id);
				//елсли в этом промежуто что то есть
				if(is_array($ans_id))
				{	//время еще не выло!!! так что пошел нахуй..
					function hms2sec ($hms) {
    					list($h, $m, $s) = explode (":", $hms);
				    	$seconds = 0;
				    	$seconds += (intval($h) * 3600);
				    	$seconds += (intval($m) * 60);
				    	$seconds += (intval($s));
				    	return $seconds;
					}
					$time0 = hms2sec($time_x);
					$time1 = time();
					///НЕПОНЯТНО ОДИН ЧАС убавляет ПРОБЛЕМА В ЧАСОВЫХ ПОЯСАХ $time2
					$time2 = strtotime($ans_id[time_stamp])+3600;
					$ssii1 = ($time2 + $time0) - $time1;
					$time = new DateTime("@".$ssii1);
					$ssii1 = $time->format('H:i:s');
					//ВРЕМЯ ЕЩЕ НЕ ВЫШЛО СТОЙ!!!
					echo json_encode(array("time"=>$ssii1,"suka"=>"0","mac"=>$mac_address_client,"num"=>"1","rekl_id"=>$_POST[idd]));
					return;
				}
				else
				{	//Время вышло все нормально записываем num=1 user_id time_stamp
					mysql_query("INSERT INTO `mydb`.`answer_users` (`user_id`, `rekl_id`, `num`, `mac`) VALUES ('$user_id10', '$_POST[idd]', '1', '$mac_address_client');");
				}
			}
			//ПРОВЕВЯЕМ num2
			//$_POST[mac] = $mac_address_client;
			if($_POST[num]==2)
			{	//выводим был ли num1 от этого user_id на эту rekl_id за последние Y время
				//сортировка по убыванию времени с лимитом 1
				$gh2 = "SELECT * FROM mydb.answer_users WHERE num=1 AND rekl_id=$_POST[idd] AND user_id=$user_id10 AND TIMESTAMPDIFF(second,time_stamp,current_timestamp) <= TIME_TO_SEC('$time_y') ORDER BY time_stamp DESC limit 1";
				$gh2 = mysql_query($gh2);
				$gh2 = mysql_fetch_assoc($gh2);
				//print_r($gh2);
				if(is_array($gh2))
				{
					///Время Y Не вышло значит все в норме!
					mysql_query("INSERT INTO `mydb`.`answer_users` (`user_id`, `rekl_id`, `num`, `mac`) VALUES ('$user_id10', '$_POST[idd]', '2', '$mac_address_client');");
				}
				else
				{
					///Время Y вышло ПАШЕЛ НА ХУЙ
					echo json_encode(array("time"=>"Между ответами слишком много времени прошло","suka"=>"1"));
					return;
				}
			}
			//ТОЖЕ САМОЕ ДЛЯ НУМ 3
			if($_POST[num]==3)
			{
				$gh3 = "SELECT * FROM mydb.answer_users WHERE num=2 AND rekl_id=$_POST[idd] AND user_id=$user_id10 AND TIMESTAMPDIFF(second,time_stamp,current_timestamp) <= TIME_TO_SEC('$time_y') ORDER BY time_stamp DESC limit 1";
				$gh3 = mysql_query($gh3);
				$gh3 = mysql_fetch_assoc($gh3);
				if(is_array($gh3))
					{
					///Время Не вышло
					//ПРОВЕРИТЬ ПРОМЕЖУТОК ЕСть ли ответ на этот же вопрос
					//этим же человеком в течении промежута Y
					$gh133 = "SELECT * FROM mydb.answer_users WHERE num=3 AND ((user_id=$user_id10 OR mac='$mac_address_client') OR (user_id=$user_id10 AND mac='$mac_address_client')) AND TIMESTAMPDIFF (second,time_stamp,current_timestamp) <= TIME_TO_SEC('$time_y') ORDER BY time_stamp DESC limit 1";
					$gh134 = mysql_query($gh133);
					$gh135 = mysql_fetch_assoc($gh134);
					//елсли в этом промежуто что то есть
					if(is_array($gh135))
					{
						//////////////////////////////////////
						//// ВНИМАНИЕ ХАК пытается много  ////
						//// раз ответить за промежуток Y ////
						////    на посдений вопрос        ////
						//////////////////////////////////////
 						//echo json_encode(array("suka"=>"2"));
						//раскоментировать если хошь предупредить хакера...
						return;
					}
					else
					//Если за промежуток Y никто не отвечал то добавить ЗАПИСЬ!!
					{
						mysql_query("INSERT INTO `mydb`.`answer_users` (`user_id`, `rekl_id`, `num`, `mac`) VALUES ('$user_id10', '$_POST[idd]', '3', '$mac_address_client');");
					}
				}
				else
				{
					///Время вышло
					echo json_encode(array("time"=>"Между ответами слишком много времени прошло","suka"=>"1"));
					return;
				}
			}

			$KEY = 1;
			$_POST[num] = $_POST[num] + 1;
			if($_POST[num]>3)
			//если количество больше ТРЕХ то это последняя хуйня
			{
				$result2 = mysql_query("SELECT redirect FROM mydb.rekl_id WHERE rekl_id=\"".$_POST[idd]."\"");
				$sss = mysql_fetch_array($result2);
				$row2=array("redirect"=>$sss[redirect],"ok"=>"ok");
				echo json_encode($row2);
				//вычислить рестрикт и рестриктред и овнер айди
				$result3 = mysql_query("SELECT `restrict`,`restrictred`,`owner_id` FROM mydb.rekl_id WHERE rekl_id=\"".$_POST[idd]."\"");
				$row1=mysql_fetch_assoc($result3);
				$count = $row1[restrict] - 1;

				//СОКРАТИТЬ ДЕНЬГИ...
				//найти баланс ОВНЕРА
				$sss = mysql_query("SELECT coin_summ FROM mydb.coin_summ WHERE user_id=$row1[owner_id]");
				$rww=mysql_fetch_assoc($sss);
				$summ = $rww[coin_summ];
				//СНЯТЬ С БАЛАНСА
				if($summ > 0)
				{
					//Вычисляет стоимость показа $g1[cost]
					$g1 = mysql_query("SELECT cost FROM mydb.cost WHERE cost_id=0");
					$g1 = mysql_fetch_assoc($g1);
					//вычесть стоимость
					$summ = $summ - $g1[cost];
					//обновить баланс
					mysql_query("UPDATE mydb.coin_summ SET coin_summ=$summ WHERE user_id=$row1[owner_id]");
				}
				else
				{
					////////////////////////////////////////
					///БАЛАНС НЕ СОВПАДАЕТ С РЕСТРИКТОМ!!///
					////////////////////////////////////////
				}
				//СОКРАТИТЬ РЕСТРИКТ на 1 Добавить РЕСТРИКТЕД НА 1
        	                if($count<0)
	                        {
                                	//если ниже нуля
					if($_POST[idd]==0)
					{
						/////////////////////////////////
						////ПОЧЕМУ ПИШЕТ ТОЛЬКО 2????////
						/////////////////////////////////

						$countt = $row1[restrictred];
						$countt = $countt + 1;
						$ssff3 = "UPDATE mydb.rekl_id SET restrictred=\"".$countt."\" WHERE rekl_id=0";
						mysql_query($ssff3);
					}
                        	        return;
                	        }
				else
				{
					//если выше нуля
					$countt = $row1[restrictred];
					$countt = $countt + 1;
					mysql_query("UPDATE `mydb`.`rekl_id` SET `restrict`=\"".$count."\" WHERE rekl_id=\"".$_POST[idd]."\"");
					mysql_query("UPDATE `mydb`.`rekl_id` SET restrictred=\"".$countt."\" WHERE rekl_id=\"".$_POST[idd]."\"");
					return;
				}
			}
			else
			{
				goto a;
			}
		}
		        else
		//если капча ошибочна
		{
                	//ЧТО ДЕЛАТЬ???
		        $KEY = 0;
        	}
	}
	else
	{
		//ЕЛСИ ПУСТО MAC IDD NUM
	}
}
else
//если парль от куки не верны...
{
	echo json_encode("error");
	setcookie ("id", "", 1);
	return;
}
a:
$result1 = mysql_query("SELECT rekl_id FROM mydb.rekl_id WHERE `restrict`>0 order by rekl_id limit 1");
while ($row[]=mysql_fetch_array($result1)){}
$rekk = $row[0]['rekl_id'];
if ($rekk === null)
{
$countt = $row1['restrictred'];
$countt = $countt + 1;
mysql_query("UPDATE `mydb`.`rekl_id` SET restrictred=\"".$countt."\" WHERE rekl_id=\"".$_POST['idd']."\"");
$result33 = mysql_query("SELECT title,text,answer,hint,rekl_id,num FROM mydb.rekl_page WHERE rekl_id=0 AND num=\"".$_POST['num']."\"");
while ($rodd[]=mysql_fetch_array($result33)){}
$rekl = $rodd[0];
$rekl = array_merge($rekl, array("captcha" => "$KEY", "login"=>$ss[0], "mac"=>$mac_address_client));
echo json_encode($rekl);
return;
}
$result33 = mysql_query("SELECT title,text,answer,hint,rekl_id,num FROM mydb.rekl_page WHERE rekl_id=$rekk AND num=\"".$_POST[num]."\"");
while ($rodd[]=mysql_fetch_array($result33)){}
$rekl = $rodd[0];
$rekl = array_merge($rekl, array("captcha" => "$KEY", "login"=>$ss[0], "mac"=>$mac_address_client));
echo json_encode($rekl);
?>
