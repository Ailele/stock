<?php 
	namespace Stock\Caculate;

	class Caculate 
	{
		protected $stockInfo;
		protected $commissionList;

		public function __construct(array $StockInfo)
		{
			$this -> stockInfo = $StockInfo;
		}

		
		public function commission(array $StockInfo)
		{
			$totalBuy = 0;
			$totalSell = 0;
			for($idx = 10; $idx < 20; $idx += 2)
			{
				$totalBuy += $StockInfo[$idx];
			}

			for($idx = 20; $idx < 30; $idx += 2)
			{
				$totalSell += $StockInfo[$idx];
			}

			$commission = ($totalBuy - $totalSell) / 100;
			return number_format($commission, 2);
		}

		public function commissionList()
		{
			$stockList = $this -> stockInfo;
			$list = array();
			foreach ($stockList as $value) 
			{
				$list[$value[0]] = $this -> Commission($value);
			}
			asort($list);

			return $this -> commissionList = $list;
		}

		public function getAvg($historyData)
		{
			$result = array();
			$result['stockCode'] = $historyData[0];
			$count = count($historyData[1]);
			$raise = 1.0;
			$price = 0.0;

			foreach ($historyData[1] as  $value) 
			{
				$raise *= (1 + (float)$value[4] / 100);
				$price += (float)$value[2];
			}

			$weekRaise = 1.0;
			$weekPrice = 0.0;

			for($day = 0; $day < 6; $day++)
			{
				$value = $historyData[1][$day];
				$weekRaise *= (1 + (float)$value[4] / 100);
				$weekPrice += (float)$value[2];
			}

			$result['raise'] = number_format($raise, 2) - 1;
			$result['avgPrice'] = number_format($price / $count, 2);
			$result['weekRaise'] = number_format($weekRaise, 2) - 1;
			$result['weekPrice'] = number_format($weekPrice / 6, 2);

			return $result;
		}
	}