BitX PHP Client
===

This is a PHP client for the BitX API (https://bitx.co/api).

The client uses Guzzle to make HTTPS calls.

Reference
---


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
