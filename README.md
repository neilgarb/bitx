BitX PHP Client
===============

[![Build Status](https://travis-ci.org/neilgarb/bitx.svg?branch=master)](https://travis-ci.org/neilgarb/bitx)

This is a PHP client for the BitX API (https://bitx.co/api).

The client uses Guzzle to make HTTPS calls.

## Installation

```bash
composer require "neilgarb/bitx"
```

## Usage

```php
$client = new Bitx\Client('key', 'secret');
$tickers = $client->getTickers();
```


## Reference

```php
getTicker($pair)
getTickers()
getOrderbook($pair)
getTrades($pair)
createAccount($currency, $name)
getBalance()
getTransactions($account, $min_row, $max_row)
getOrders($state = null, $pair = null)
createOrder($pair, $type, $volume, $price)
createMarketOrder(
stopOrder($order_id)
getOrder($order)
getFundingAddress($asset, $address = null)
createFundingAddress($asset)
getWithdrawals()
createWithdrawal($type, $amount)
getWithdrawal($withdrawal)
deleteWithdrawal($withdrawal)
createSend(
createQuote($type, $base_amount, $pair)
getQuote($quote)
exerciseQuote($quote)
```
