<?php

/**
 * BitX PHP client
 *
 * https://bitx.co/api
 *
 * THIS CODE AND INFORMATION IS PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A PARTICULAR
 * PURPOSE. IT CAN BE DISTRIBUTED FREE OF CHARGE AS LONG AS THIS HEADER
 * REMAINS UNCHANGED.
 */

namespace Bitx;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;

class Client
{
    /** @var string */
    protected $url;

    /** @var string */
    protected $version;

    /** @var string */
    protected $key;

    /** @var string */
    protected $secret;

    /** @var HttpClient */
    protected $client;

    /**
     * @param string $key
     * @param string $secret
     */
    public function __construct($key, $secret)
    {
        $this->setUrl('https://api.mybitx.com');
        $this->setVersion('1');
        $this->setAuth($key, $secret);
        $this->setClient(new HttpClient());
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = rtrim($url, '/');
        return $this;
    }

    /**
     * @param string $version
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @param string $key
     * @param string $secret
     * @return $this
     */
    public function setAuth($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
        return $this;
    }

    /**
     * @param HttpClient $client
     * @return $this
     */
    public function setClient(HttpClient $client)
    {
        $this->client = $client;
        return $this;
    }

    //--------------------------------------------------------------------------
    // HTTP methods
    //--------------------------------------------------------------------------

    /**
     * @param string $path
     * @return string
     */
    protected function buildUrl($path)
    {
        return sprintf(
            '%s/api/%s/%s',
            $this->url,
            $this->version,
            ltrim($path, '/')
        );
    }

    /**
     * @param array $options
     * @return array
     */
    protected function buildOptions(array $options = [])
    {
        return array_merge([
            'auth' => [$this->key, $this->secret],
        ], $options);
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $options
     * @return mixed
     * @throws Exception
     */
    protected function request($method, $url, array $options)
    {
        try {
            $res = $this->client->request($method, $url, $options);
        } catch (ClientException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
        $json = json_decode($res->getBody());
        if (isset($json->error)) {
            throw new Exception(
                sprintf('%s (%s)', $json->error, $json->error_code),
                $res->getStatusCode()
            );
        }
        return $json;
    }

    /**
     * @param string $path
     * @param array $data
     * @return mixed
     */
    public function get($path, array $data = [])
    {
        $url = $this->buildUrl($path);
        $options = $this->buildOptions([
            'query' => $data,
        ]);
        return $this->request('GET', $url, $options);
    }

    /**
     * @param string $path
     * @param array $data
     * @return mixed
     */
    public function post($path, array $data = [])
    {
        $url = $this->buildUrl($path);
        $options = $this->buildOptions([
            'form_params' => $data,
        ]);
        return $this->request('POST', $url, $options);
    }

    /**
     * @param string $path
     * @param array $data
     * @return mixed
     */
    public function put($path, array $data = [])
    {
        $url = $this->buildUrl($path);
        $options = $this->buildOptions([
            'form_params' => $data,
        ]);
        return $this->request('PUT', $url, $options);
    }

    /**
     * @param string $path
     * @param array $data
     * @return mixed
     */
    public function delete($path, array $data = [])
    {
        $url = $this->buildUrl($path);
        $options = $this->buildOptions([
            'query' => $data,
        ]);
        return $this->request('DELETE', $url, $options);
    }

    //--------------------------------------------------------------------------
    // API methods
    //--------------------------------------------------------------------------

    /**
     * @param $pair
     * @return mixed
     */
    public function getTicker($pair)
    {
        return $this->get('/ticker', ['pair' => $pair]);
    }

    /**
     * @return mixed
     */
    public function getTickers()
    {
        return $this->get('/tickers');
    }

    /**
     * @param $pair
     * @return mixed
     */
    public function getOrderbook($pair)
    {
        return $this->get('/orderbook', ['pair' => $pair]);
    }

    /**
     * @param $pair
     * @return mixed
     */
    public function getTrades($pair)
    {
        return $this->get('/trades', ['pair' => $pair]);
    }

    /**
     * @param $currency
     * @param $name
     * @return mixed
     */
    public function createAccount($currency, $name)
    {
        return $this->post('/accounts', [
            'currency' => $currency,
            'name' => $name,
        ]);
    }

    /**
     * @return mixed
     */
    public function getBalance()
    {
        return $this->get('/balance');
    }

    /**
     * @param $account
     * @param $min_row
     * @param $max_row
     * @return mixed
     */
    public function getTransactions($account, $min_row, $max_row)
    {
        $path = sprintf('/accounts/%s/transactions', $account);
        return $this->get($path, [
            'min_row' => $min_row,
            'max_row' => $max_row,
        ]);
    }

    /**
     * @param $state
     * @param $pair
     * @return mixed
     */
    public function getOrders($state = null, $pair = null)
    {
        return $this->get('/listorders', [
            'state' => $state,
            'pair' => $pair,
        ]);
    }

    /**
     * @param $pair
     * @param $type
     * @param $volume
     * @param $price
     * @return mixed
     */
    public function createOrder($pair, $type, $volume, $price)
    {
        return $this->post('/postorder', [
            'pair' => $pair,
            'type' => $type,
            'volume' => $volume,
            'price' => $price,
        ]);
    }

    /**
     * @param $pair
     * @param $type
     * @param $counter_volume
     * @param $base_volume
     * @return mixed
     */
    public function createMarketOrder(
        $pair,
        $type,
        $counter_volume,
        $base_volume
    )
    {
        return $this->post('/marketorder', [
            'pair' => $pair,
            'type' => $type,
            'counter_volume' => $counter_volume,
            'base_volume' => $base_volume,
        ]);
    }

    /**
     * @param $order_id
     * @return mixed
     */
    public function stopOrder($order_id)
    {
        return $this->post('/stoporder', [
            'order_id' => $order_id,
        ]);
    }

    /**
     * @param $order
     * @return mixed
     */
    public function getOrder($order)
    {
        $path = sprintf('/orders/%s', $order);
        return $this->get($path);
    }

    /**
     * @param $asset
     * @param $address
     * @return mixed
     */
    public function getFundingAddress($asset, $address = null)
    {
        return $this->get('/funding_address', [
            'asset' => $asset,
            'address' => $address,
        ]);
    }

    /**
     * @param $asset
     * @return mixed
     */
    public function createFundingAddress($asset)
    {
        return $this->post('/funding_address', ['asset' => $asset]);
    }

    /**
     * @return mixed
     */
    public function getWithdrawals()
    {
        return $this->get('/withdrawals');
    }

    /**
     * @param $type
     * @param $amount
     * @return mixed
     */
    public function createWithdrawal($type, $amount)
    {
        return $this->post('/withdrawals', [
            'type' => $type,
            'amount' => $amount,
        ]);
    }

    /**
     * @param $withdrawal
     * @return mixed
     */
    public function getWithdrawal($withdrawal)
    {
        $path = sprintf('/withdrawals/%s', $withdrawal);
        return $this->get($path);
    }

    /**
     * @param $withdrawal
     * @return mixed
     */
    public function deleteWithdrawal($withdrawal)
    {
        $path = sprintf('/withdrawals/%s', $withdrawal);
        return $this->delete($path);
    }

    /**
     * @param $amount
     * @param $currency
     * @param $address
     * @param $description
     * @param $message
     * @return mixed
     */
    public function createSend(
        $amount,
        $currency,
        $address,
        $description = null,
        $message = null
    )
    {
        return $this->post('/send', [
            'amount' => $amount,
            'currency' => $currency,
            'address' => $address,
            'description' => $description,
            'message' => $message,
        ]);
    }

    /**
     * @param $type
     * @param $base_amount
     * @param $pair
     * @return mixed
     */
    public function createQuote($type, $base_amount, $pair)
    {
        return $this->post('/quotes', [
            'type' => $type,
            'base_amount' => $base_amount,
            'pair' => $pair,
        ]);
    }

    /**
     * @param $quote
     * @return mixed
     */
    public function getQuote($quote)
    {
        $path = sprintf('/quotes/%s', $quote);
        return $this->get($path);
    }

    /**
     * @param $quote
     * @return mixed
     */
    public function exerciseQuote($quote)
    {
        $path = sprintf('/quotes/%s', $quote);
        return $this->put($path);
    }

    /**
     * @param $quote
     * @return mixed
     */
    public function discardQuote($quote)
    {
        $path = sprintf('/quotes/%s', $quote);
        return $this->delete($path);
    }
}
