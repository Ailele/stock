<?php
	namespace Stock\Configure;

	class Configure
	{
		protected $configure = array();
		protected $envPath;

		public function __construct($env = 'env.txt')
		{
			if (!file_exists($env))
			{
				exit('environment file not exist');
			}
			$this -> env = $env;
			$this -> configure = $this -> initConfig($env);
		}

		public function initConfig($env)
		{
			$fileHandle = fopen($env, 'r');
			$configure = array();
			while ($config = fgets($fileHandle))
			{
				$config = trim($config);
				if(!empty($config))
				{
					list($name, $value) = preg_split('/=/', $config);
					$name = trim($name);
					$value = trim($value);
					$configure[$name] = $value;
				}
			}

			return $configure;
		}


		public function configure()
		{
			$args = func_get_args();
			if(2 == func_num_args())
			{
				return $this -> configure[$args[0]] = $args[1];
			}
			else
			{
				return $this -> configure[$args[0]];
			}
		}

		public function getConfigure()
		{
			return $this -> configure;
		}
	}