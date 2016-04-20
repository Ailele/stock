<?php
	require_once 'autoload.php';
	error_reporting(0);
	if(isset($_GET['update']) )
	{
		run(40, true);
	}

	if(isset($_GET['type']) && isset($_GET['base']) &&isset($_GET['top']) && isset($_GET['price']))
	{
		$type = $_GET['type'];
		$base = $_GET['base'];
		$top =  $_GET['top'];
		$price = $_GET['price'];
		$result = filter($type, $base, $top, $price);
		switch ($type)
		{
			case 'raise':
				$time = '本月';
				break;
			case 'weekRaise':
				$time = '本周';
				break;
			case 'todayRaise':
				$time = '本日';
				break;
			default:
				break;
		}
	}
	else
	{
		$result = filter('raise', 0.2, 0.3, 10);
		$base = 0.2;
		$top = 0.3;
		$price = 10;

		$time = '本周';
	}
	$count = count($result);
?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div id="header">
	<div id="search">
		<form id="se" action="stock.php" method="get">
			时间
			<select name="type">
				<option  value="todayRaise">今天</option>
				<option selected value="weekRaise">一周</option>
				<option  value="raise">一个月</option>
			</select>
			低<input type="text" name="base"/>
			涨<input type="text" name="top" />
			价<input type="text" name="price" value="10" />
			<input type="submit" value="开始搜索" />
		</form>
		<form id="update" action="stock.php">
			<input type="submit" name="update" value="update" />
		</form>
	</div>
</div>
<div id="container">
	<div id="infoTable">
		<table border="0" cellpadding="0" cellspacing="0">
			<?php echo "<br/>$time 涨幅 ".(100 * $base)."% 到 ".(100 * $top)."% 股价小于".$price."元&nbsp;&nbsp;$count&nbsp;个<br/><br/>"; ?>
			<tr id="tableTitle">
				<th>代码</th>
				<th>当前价</th>
				<th>周均价</th>
				<th>月均价</th>
				<th>今涨幅</th>
				<th>周涨幅</th>
				<th>月涨幅</th>
				<th>委差</th>
			</tr>
			<?php
				foreach($result as $stock)
				{
					$info = $stock['info'];
					$code = substr($info[0], 2);
					$html=<<<HTML
					<tr id="$info[0]" class="stock">
						<td><a href="http://stockhtm.finance.qq.com/sstock/ggcx/$code.shtml">$info[0]</a></td>
						<td>$info[1]</td>
						<td>$info[2]</td>
						<td>$info[3]</td>
						<td>$info[4]</td>
						<td>$info[5]</td>
						<td>$info[6]</td>
						<td>$info[7]</td>
					</tr>
HTML;
					echo $html;
				}
			?>
		</table>
	</div>
	<div id="rightImg">
		<img id="img" src="" />
	</div>
</div>
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript" src="stock.js"></script>
</body>
</html>
