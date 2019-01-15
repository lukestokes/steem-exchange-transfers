# Steem Exchange Transfers

## Purpose:

  To bring perspective and transparency to the exchange transfer activity of accounts on the Steem blockchain. Some people see the Steem blockchain as a mechanism to extract value. Others see it as a long-term investment in a decentralized, censorship-resistant, immutable blockchain-based communication and data storage platform. The intention of this data is to help inform our perspectives of various accounts on the Steem blockchain.

## Description:

  `steem_exchange_transfers.php` is a script which uses from <a href="http://steemsql.com/">SteemSQL</a> and historical USD value for SBD and STEEM to calculate how much USD value various accounts have transferred from their accounts to an exchange (a withdrawal from the Steem blockchain represented by a negative number) or transferred from an exchange to their accounts (a deposit into the Steem blockchain represented by a positive number).

## Methodology and Process Explanation:

* Historical prices for SBD and STEEM were obtained by taking the average of the open and close for a given day based on CMC data. You can find this data in `historical_sbd_price.csv` and `historical_steem_price.csv`

* STEEM and SBD exchange transfers were obtained using the following query:

	```
	SELECT [from], [to], sum(amount) as total_amount, DAY(timestamp) as day, MONTH(timestamp) as month, YEAR(timestamp) as year FROM TxTransfers WITH (NOLOCK)
	where amount_symbol = 'SBD' and [type] = 'transfer' and
	([from] in ('poloniex', 'bittrex', 'blocktrades', 'openledger', 'openledger-dex','hitbtc-exchange', 'hitbtc-payout', 'changelly', 'freewallet.org', 'freewallet', 'coinpayments.net', 'rudex', 'binance-hot', 'deepcrypto8', 'steemexchanger', 'upbit-exchange', 'myupbit', 'upbitsteemhot', 'upbituserwallet', 'gopax', 'gopax-deposit', 'huobi-pro', 'huobi-withdrawal', 'bithumb.hot')
	 or [to] in ('poloniex', 'bittrex', 'blocktrades', 'openledger', 'openledger-dex','hitbtc-exchange', 'hitbtc-payout', 'changelly', 'freewallet.org', 'freewallet', 'coinpayments.net', 'rudex', 'binance-hot', 'deepcrypto8', 'steemexchanger', 'upbit-exchange', 'myupbit', 'upbitsteemhot', 'upbituserwallet', 'gopax', 'gopax-deposit', 'huobi-pro', 'huobi-withdrawal', 'bithumb.hot'))
	AND YEAR(timestamp) = 2018
	GROUP BY [from], [to], DAY(timestamp), MONTH(timestamp), YEAR(timestamp)
	order by YEAR(timestamp), MONTH(timestamp), DAY(timestamp) asc
	```

  With values for amount_symbol changing between "SBD" and "STEEM" for each YEAR(timestamp) change (2018 is displayed in this example).

	The output of these queries are stored in the `<year>_<currency>_exchange_transfers.csv` files such as `2018_steem_exchange_transfers.csv`

	The query looks at all transfers where either the from or to involves a known exchange wallet and sums those amounts per day, per account. For example, if you transfer 100 STEEM to an exchange, then another 40 STEEM, and then transfer 90 STEEM back from an exchange on the same day, what will be recorded from the query is 140 STEEM in withdrawals and 90 STEEM in depsoits for that day. The code will then net those out for the account as a 50 STEEM withdrawal.

* The `steem_exchange_transfers.php` script parses each day and keeps a running total of withdrawals and deposits for each account using USD values for STEEM or SBD on that day.

## Why:

  You might ask why I bothered to do this. For years now, I've been running the weekly exchange transfer report. Here's <a href="https://steemit.com/exchangereport/@lukestokes/exchange-transfer-report-1-6-2018-to-1-12-2018">an example</a>. I view the value of the Steem blockchain as a sort of shared collaborative commons. Though we each have our own stake and property on the blockchain, our actions impact the value of everyone else's stake. Unlike most blockchains, the inflation here is being distributed via a rewards pool which we share a bit of responsibility in protecting using "proof of brain" as described in the Steem white paper.

  When value from the rewards pool is distributed to Steem accounts, what they do with that Steem impacts everyone. If they regularly dump it on the market, that creates sell pressure for the STEEM and SBD tokens which could lower the value of everyone else's investment. If, on the other hand, they hold their tokens and buy more, it creates buy pressure which could increase the value of everyone else's investment. Without the actual data, it's not easy to determine who is extracting value and who is adding value. It's also important to keep in mind that transferring value to an exchange does not automatically mean the account sold on that exchange.
  
## Results:
 
To view the results, check out the following files:
 
* <a href="https://raw.githubusercontent.com/lukestokes/steem-exchange-transfers/master/2016_STEEM_and_SBD_investors.txt">2016_STEEM_and_SBD_investors.txt</a>
* <a href="https://raw.githubusercontent.com/lukestokes/steem-exchange-transfers/master/2017_STEEM_and_SBD_investors.txt">2017_STEEM_and_SBD_investors.txt</a>
* <a href="https://raw.githubusercontent.com/lukestokes/steem-exchange-transfers/master/2018_STEEM_and_SBD_investors.txt">2018_STEEM_and_SBD_investors.txt</a>
 
Negative numbers mean the account withdrew more value from their account to an exchange than they deposited from an exchange in a given year. Positive numbers mean the account deposited more value to their account from an exchange they withdrew from their account.

# I CAN NOT GUARANTEE THE ACCURACY OF THIS DATA

Please, assume this information is not accurate until you've gone through the code and verified it yourself. If you see a bug or a problem with how this data is collected, please let me know by opening an issue ticket or submitted a pull request to fix it.
