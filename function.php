<?php 
	
	//返回距离之前多少天的日期
	function getBeforeDate($day)
	{
		$now = date('Y-m-d', time());
		$today =  new \DateTime($now);
		$interval = new \DateInterval('P'.$day.'D');
		$interval -> invert = 1;
		$today -> add($interval);
		return $today -> format('Ymd');
	}

	function selectCode($stockCode)
	{
			$prefix = substr($stockCode, 0, 2);
			if('00' == $prefix || '30' == $prefix)
			{
				$stockCode .= 'sz';
			}
			else if('60' == $prefix)
			{
				$stockCode .= 'sh';
			}

			$sql = "select * from stock where stockCode = '$stockCode';";
			$mysqli = new mysqli('localhost', 'root', '12341234', 'stock');
			$query = $mysqli -> query($sql);
			$result = $query -> fetch_all(MYSQLI_ASSOC);
			$result = $result[0];

			$info = array();
			$info['stock'] = $stockCode;
			$info['info'] = array( $result['stockCode'], 
								   $result['price'], 
								   $result['weekPrice'],
								   $result['avgPrice'],
							       $result['weekRaise'],
							       $result['raise'],
								   $result['commission'], 
							       $result['totalDeal']
							       );

			return $info;
	}

	function select($raiseType, $raise, $top = null, $price = 10)
	{
			if(!$top)
			{
				$sql = "select * from stock where $raiseType > $raise and price < $price order by $raiseType desc;";
			}
			else
			{
				$sql = "select * from stock where $raiseType > $raise and $raiseType < $top and price < $price order by $raiseType desc;";
			}
			$mysqli = new mysqli('localhost', 'root', '12341234', 'stock');
			$query = $mysqli -> query($sql);
			$result = $query -> fetch_all(MYSQLI_ASSOC);
			$stockInfo = array();

			if(!$result)
			{
				return false;
			}
			else
			{
				foreach ($result as $value) 
				{
					$info = array();
					$info['stock'] = $value['stockCode'];
					$info['info'] = array( $value['stockCode'], 
											 $value['price'], 
											 $value['weekPrice'],
											 $value['avgPrice'],
						                     $value['todayRaise'],
										     $value['weekRaise'],
										     $value['raise'],
											 $value['commission'],
										     $value['totalDeal']
										     );

					$stockInfo[] = $info;
				}
			}
			return $stockInfo;
	}

	function filter($filterType, $raise, $top, $price = 10)
	{
		$data = select($filterType, $raise, $top, $price);
		$result = array();
		foreach ($data as $stock) 
		{
			if($stock['info'][2] < $stock['info'][3] * 1.25 && $stock['info'][1] > $stock['info'][3])
			{
				$result[] = $stock;
			}
		}

		return $result;
	}

	function getImageURL($stockCode, $baseURL)
	{
		return $baseURL."$stockCode.gif";
	}


	function run($day, $update, $command = '')
	{
		$startTime = microtime(true);
		$config = new  Stock\Configure\Configure('env.txt');
		$cfg = $config -> getConfigure();

		$database = new Stock\Data\Data($cfg);
		$date = $database -> checkTime();
		$now = date('Y-m-d', time());
		if('cli' == strtolower($command))
		{
			echo "Last update time $date\n<br/>$day day data<br/>\n---------------------------------------------------------------------------------------------------\n<br/>";
		}
		if($date !== $now || $update)
		{
			$database -> clear();
			$Spider = new Stock\Spider\Spider($cfg);
			$Spider -> setPattern($cfg['PATTERN']);
			$stocklist = $Spider -> getStockList();
			$Caculate = new Stock\Caculate\Caculate($stocklist);

			$today = getBeforeDate(0);
			$before = getBeforeDate($day);
			
			$i = 0;
			$total = count($stocklist);

			foreach ($stocklist as $stock)
			{
				$stockNP = substr($stock, 2);
				$historyData = $Spider -> getHistoryData($stockNP, $before, $today);
				$stockData = $Spider -> getStockData($stock);
				if(!$historyData || !$stockData)
				{
					continue;
				}
				else
				{
					$avg = $Caculate -> getAvg($historyData);
					$todayRaise = number_format(($stockData[3] - $stockData[2]) / $stockData[2], 2);
					$database -> storeStock('stock', array('stockCode' => $stock, 
														   'price' => $stockData[3], 
														   'todayRaise' => $todayRaise,
														   'weekPrice' => $avg['weekPrice'],
														   'avgPrice' => $avg['avgPrice'],
														   'weekRaise' => $avg['weekRaise'],
														   'raise' => $avg['raise'],
														   'totalDeal' => number_format(($stockData[9] / 10000), 2),
														    'commission' => $Caculate -> commission($stockData)
															));
					$database -> storeHistory('history', array('stockCode' => $stock, 'json' => json_encode($historyData)));
					if('cli' == strtolower($command))
					{
						echo sprintf("%-4d %10s %10s %10.2f %10.2f %10.2f %10.2f %10.2f", $i++, $stock, $stockData[3], $avg['weekPrice'], $avg['avgPrice'],  $todayRaise, $avg['weekRaise'], $avg['raise']);

						echo "\ttime:".number_format(microtime(true) - $startTime, 0),"s [$i/$total]\n<br/>";
					}

				}
			}


			$endTime = microtime(true);
			$cost = $endTime - $startTime;
			echo "\n--------------------------------------\ntime: $cost(s)";
			$database -> updateTime($now);
		}
	}