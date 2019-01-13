<?PHP
class Investors {
	public $accounts = array();
	public $exchange_accounts = array('poloniex', 'bittrex', 'blocktrades', 'openledger', 'openledger-dex','hitbtc-exchange', 'hitbtc-payout', 'changelly', 'freewallet.org', 'freewallet', 'coinpayments.net', 'rudex', 'binance-hot', 'deepcrypto8', 'steemexchanger', 'upbit-exchange', 'myupbit', 'upbitsteemhot', 'upbituserwallet', 'gopax', 'gopax-deposit', 'huobi-pro', 'huobi-withdrawal', 'bithumb.hot');
	public $steem_usd_prices = array();
	public $sbd_usd_prices = array();


	public function loadHistoricSteemPrices() {		
		$data_file = 'historical_steem_prices.csv';
		$row = 1;
		if (($handle = fopen($data_file, "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		        $this->steem_usd_prices[date('Y-n-j',strtotime($data[0]))] = $data[1];
		    }
		    fclose($handle);
		}
	}

	public function loadHistoricSBDPrices() {		
		$data_file = 'historical_sbd_prices.csv';
		$row = 1;
		if (($handle = fopen($data_file, "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		        $this->sbd_usd_prices[date('Y-n-j',strtotime($data[0]))] = $data[1];
		    }
		    fclose($handle);
		}
	}

	public function getUSDSteemPrice($date) {
		if (count($this->steem_usd_prices) == 0) {
			$this->loadHistoricSteemPrices();
		}
		$price = 0;
		if (array_key_exists($date, $this->steem_usd_prices)) {
			$price = $this->steem_usd_prices[$date];
		}
		return $price;
	}

	public function getUSDSBDPrice($date) {
		if (count($this->sbd_usd_prices) == 0) {
			$this->loadHistoricSBDPrices();
		}
		$price = 0;
		if (array_key_exists($date, $this->sbd_usd_prices)) {
			$price = $this->sbd_usd_prices[$date];
		}
		return $price;
	}

	public function parseCSVData($currency,$year)
	{
		$data_file = $year . '_' . strtolower($currency) . '_exchange_transfers.csv';
		$row = 1;
		if (($handle = fopen($data_file, "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		        $this->processRow($data,$currency);
		    }
		    fclose($handle);
		}
	}

	static function cmp($a, $b)
	{
	    return ($a['TOTAL_USD_net'] >= $b['TOTAL_USD_net']);
	}

	public function outputData()
	{
		//2016
		$this->parseCSVData('STEEM','2016');
		$this->parseCSVData('SBD','2016');
	    usort($this->accounts, array('Investors', 'cmp'));
	    setlocale(LC_MONETARY,"en_US");
	    $output = '';
		foreach ($this->accounts as $account => $data) {
			$output .= str_pad($data['account'],30) . money_format('%(#10n',$data['TOTAL_USD_net']) . "\n";
		}
		file_put_contents('2016_STEEM_and_SBD_investors.txt',$output);

		// reset things for 2017
		$this->accounts = array();

		$this->parseCSVData('STEEM','2017');
		$this->parseCSVData('SBD','2017');
	    usort($this->accounts, array('Investors', 'cmp'));
	    setlocale(LC_MONETARY,"en_US");
	    $output = '';
		foreach ($this->accounts as $account => $data) {
			$output .= str_pad($data['account'],30) . money_format('%(#10n',$data['TOTAL_USD_net']) . "\n";
		}
		file_put_contents('2017_STEEM_and_SBD_investors.txt',$output);

		// reset things for 2018
		$this->accounts = array();

		$this->parseCSVData('STEEM','2018');
		$this->parseCSVData('SBD','2018');
	    usort($this->accounts, array('Investors', 'cmp'));
	    setlocale(LC_MONETARY,"en_US");
	    $output = '';
		foreach ($this->accounts as $account => $data) {
			$output .= str_pad($data['account'],30) . money_format('%(#10n',$data['TOTAL_USD_net']) . "\n";
		}
		file_put_contents('2018_STEEM_and_SBD_investors.txt',$output);
	}

	public function processRow($row, $currency)
	{
		$date = $row[5] . '-' . $row[4] . '-' . $row[3];
		// deposit
		if (in_array($row[0], $this->exchange_accounts)) {
			$this->addAccountIfNeeded($row[1]);
			$this->processDeposit($row[1],$date, $row[2],$currency);
		}
		// withdrawal
		if (in_array($row[1], $this->exchange_accounts)) {
			$this->addAccountIfNeeded($row[0]);
			$this->processWithdraw($row[0],$date, $row[2],$currency);
		}
	}

	public function addAccountIfNeeded($account)
	{
		if (!array_key_exists($account, $this->accounts)) {
			$this->accounts[$account] = array(
				'account' => $account,
				'STEEM_deposits' => 0,
				'STEEM_withdrawals' => 0,
				'SBD_deposits' => 0,
				'SBD_withdrawals' => 0,
				'TOTAL_USD_deposits' => 0,
				'TOTAL_USD_withdrawals' => 0,
				'TOTAL_USD_net' => 0
			);
		}
	}

	public function processWithdraw($account, $date, $amount, $currency) {
		$usd_amount = $amount * $this->getUSDSteemPrice($date);
		if ($currency == 'STEEM') {
			$this->accounts[$account]['STEEM_withdrawals'] += $amount;
		}
		if ($currency == 'SBD') {
			$this->accounts[$account]['SBD_withdrawals'] += $amount;
		}
		$this->accounts[$account]['TOTAL_USD_withdrawals'] += $usd_amount;
		$this->accounts[$account]['TOTAL_USD_net'] -= $usd_amount;
	}
	public function processDeposit($account, $date, $amount, $currency) {
		$usd_amount = $amount * $this->getUSDSteemPrice($date);
		if ($currency == 'STEEM') {
			$this->accounts[$account]['STEEM_deposits'] += $amount;
		}
		if ($currency == 'SBD') {
			$this->accounts[$account]['SBD_deposits'] += $amount;
		}
		$this->accounts[$account]['TOTAL_USD_deposits'] += $usd_amount;
		$this->accounts[$account]['TOTAL_USD_net'] += $usd_amount;
	}
}

$Investors = new Investors();
$Investors->outputData();