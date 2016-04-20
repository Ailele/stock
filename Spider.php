<?php 
	namespace Stock\Spider;

	class Spider 
	{
		protected $config;
		protected $stockList;
		protected $curl;
		protected $pattern;
		protected $stockInfo;

		public function __construct($config)
		{
			$this -> config = $config;
			$this -> curl = curl_init();
		}


		public function getStockList()
		{
			$URL = $this -> config['CODE_URL'];
			$this -> setOpt(CURLOPT_URL, $URL);
			$this -> setOpt(CURLOPT_RETURNTRANSFER, true);
			$page = $this -> exec();
			preg_match_all($this -> pattern, $page, $stockList);
			$stockList = $stockList[1];

			$result = array();
			foreach ($stockList as $key => $value) 
			{
				$prefix = substr($stockList[$key], 0, 4);
				if('sh60' == $prefix || 'sz00' == $prefix || 'sz30' == $prefix)
				{
					$result[] = $stockList[$key];
				}
			}
			return $this -> stockList = $result;
		}


		public function getStockData($stockCode)
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

			$API = $this -> config['API_URL'];
			$search = $API."=".$stockCode;
			$this -> setOpt(CURLOPT_URL, $search);
			$this -> setOpt(CURLOPT_RETURNTRANSFER, true);
			$this -> setOpt(CURLOPT_ENCODING, 'gzip,deflate');
			$stockInfo = $this -> exec();

			$infoStr = mb_convert_encoding($stockInfo,'UTF-8', 'GBK');
			$info = preg_split('/"/', $infoStr);
			$info = $info[1];
			if(!$info)
			{
				return false;
			}
			return explode(',', $info);
		}

		public function getHistoryData($stockCode, $start, $end)
		{
			$prefix = substr($stockCode, 0, 2);
			if('sh' == $prefix || 'sz' == $prefix)
			{
				$stockCode = substr($stockCode, 2);
			}
			
			$HISTORYAPI = $this -> config['HP'];
			$search = $HISTORYAPI."=cn_$stockCode&start=$start&end=$end";
			$this -> setOpt(CURLOPT_URL, $search);
			$this -> setOpt(CURLOPT_RETURNTRANSFER, true);
			$this -> setOpt(CURLOPT_ENCODING, 'gzip,deflate');
			$response = $this -> exec();
			if('{}' == trim($response))
			{
				return false;
			}
			$json = json_decode($response);

			if(0 != $json[0] -> status)
			{
				return false;
			}

			$code = substr($json[0] -> code, 3);
			$data[] = $code;
			$data[] = $json[0] -> hq; 

			return $data;
		}

		public function handleData()
		{
			$result = array();
			$stockList = $this -> stockList;
			$count = 1;

			foreach ($stockList as $stockCode) 
			{
				$value = $this -> getStockData($stockCode);

				if(!empty($value[0]))
				{
					echo sprintf("\t%4d:%s\t", $count++, $stockCode);
					$result[] = $value;
				}
			}

			return $this -> stockInfo = $result;
		}

		public function getStockInfo()
		{
			if(func_num_args())
			{
				return $this -> stockInfo[func_get_arg(0)];
			}

			return $this -> stockInfo;
		}

		public function setPattern($pattern)
		{
			$this -> pattern = $pattern;
		}

		public function setOpt($opt, $value)
		{
			curl_setopt($this -> curl, $opt, $value);
		}

		public function exec()
		{
			return curl_exec($this -> curl);
		}
	}