<html>
<head>
<meta charset=utf-8>
<script type="text/javascript" src="/src/jquery-1.11.3.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
$('#status').ready(function()
{	$('#status').append("<option disabled selected>Не выбрано</option>");
	$('#sss').append("<option id='sd' disabled selected>Не выбрано</option>");

        $.post("/module/reg1.php",
        function(data)
        {$.each(data, function(i, val){$('#status').append("<option value="+i+">"+val+"</option>")})}, "json");


$('#status').click(function()
{
	$('.abas').remove();
	$('#sd').remove();
        $('#sss').append("<option id='sd' disabled selected>Не выбрано</option>");
});

$('#status').click(function()
{
	var info3 = $('#status').val();
	$.post("/module/reg.php",{status: info3},function(data)
	{
	$('.abas').remove();
	$.each(data, function(i, val){$('#sss').append("<option class='abas' value="+i+">"+val+"</option>")})}, "json");
	});




$('#ok').click(function()
{
        var info3 = $('#old').val();
        var info4 = $('#status').val();
        var info5 = $('#sss').val();
        var info6 = $('input:radio[name=sex]:checked').val();
        var info7 = $('#user').val();
        var info8 = $('#pass1').val();
        var info9 = $('#pass2').val();

	if(info3 < 100)
	{
		if(info3 > 0)
		{

        if(info4 >= 0)
        {
                if(info5 > 0)
                {
		        $.post("/module/reg3.php",{user: info7, pass1: info8, pass2: info9, sex: info6, old: info3, status2: info5},function(data)
			{
			if(data == "ok")
			{
			$.post("/module/check_login.php",{user: info7, pass: info8},function(data){
///подождать загрузки!!!!
			$(document).ready(function(){
 			window.location.href = "index.php";
			});
			})
			}
			$('#ss2').remove();
			$('#sss11').append("<b><font id=ss2 color=red size=5>"+data+"<font></b>");
			});
		}
                else
                {
                        $('#ss2').remove();
                        $('#sss11').append("<b><font id=ss2 color=red size=5> выберите подкатегорию <font></b>");
                }
        }
        else
        {
		$('#ss2').remove();
		$('#sss11').append("<b><font id=ss2 color=red size=5> выберите категорию <font></b>");

        }



		}
		else
		{
                $('#ss2').remove();
                $('#sss11').append("<b><font id=ss2 color=red size=5> укажите возраст<font></b>");

		}
	}
	else
	{
		$('#ss2').remove();
		$('#sss11').append("<b><font id=ss2 color=red size=5> укажите возраст<font></b>");

	}

})})});
</script>
</head>
<body>
<center>Регистрация</center>
Введите имя пользователья: <br><input type='text' id='user' name='user' size='10'>
<br>Введите пароль:<br> <input type='password' name='pass1' id='pass1' size='10'>
<br>Введите подтверждение пароля: <br><input type='password' name='pass2' id='pass2' size='10'>
<br><br>Выберите пол:
<div class="list"><input type='radio' id='sex' name='sex' value='1'> Мужской <input id='sex' type='radio' name='sex' value='2'>Женский<br></div>
<br>Введите возраст: <input type='select' name='old' id='old' size='2'>
<br><br>Социальный статус: <select id="status" name=status><select><select id="sss" name=status></select>
<div id=sss11></div>
<br><button id="ok">ACCEPT</button>
</body>
</html>
