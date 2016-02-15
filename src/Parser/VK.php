<?php

namespace Parser;

class VK
{
    const OAUTH_URL = 'https://oauth.vk.com';
    const AUTHORIZE_URL = '/authorize?';
    const ACCESS_TOKEN_URL = '/access_token?';
    const TOKEN_URL = '/token?';
    const API_URL = 'https://api.vk.com';

    private $access_token;
    private $secret;
    private $params;
    private $format = '';

    public function __construct($access_token = null, $secret = null, $params = array())
    {
        $this->access_token = $access_token;
        $this->secret = $secret;
        $this->params = array_merge(array(
            'lang' => 'ru',
            'v' => '5.45',
            'https' => 0
        ), $params);
    }

    public function setXml()
    {
        $this->format = '.xml';
        return $this;
    }

    public function authSites($client_id, $client_secret, $scope, $redirect_uri, $display = 'page')
    {
        if (!isset($_GET['access_token'])) {
            $params = array(
                'client_id' => $client_id,
                'scope' => $scope,
                'redirect_uri' => $redirect_uri,
                'response_type' => 'code',
                'v' => $this->params['v'],
                'display' => $display
            );
            header('Location: ' . self::OAUTH_URL . self::AUTHORIZE_URL . http_build_query($params));
        }
        if (isset($_GET['code'])) {
            $params = array(
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'code' => $_GET['code'],
                'redirect_uri' => $redirect_uri
            );
            $response = file_get_contents(self::OAUTH_URL . self::TOKEN_URL . http_build_query($params));
            $response = json_decode($response, true);
            try {
                if (isset($response['error'])) {
                    throw new \Exception($response['error_description']);
                }
            } catch (\Exception $e) {
                echo $response['error'] . ': ' . $e->getMessage();
            }
            return $response;
        }
    }

    public function authMobile($client_id, $scope, $redirect_uri, $display = 'page')
    {
        if (!isset($_GET['access_token'])) {
            $params = array(
                'client_id' => $client_id,
                'scope' => $scope,
                'redirect_uri' => $redirect_uri,
                'display' => $display,
                'v' => $this->params['v'],
                'response_type' => 'token'
            );
            header('Location: ' . self::OAUTH_URL . self::AUTHORIZE_URL . http_build_query($params));
        }
    }

    public function authServer($client_id, $client_secret)
    {
        $params = array(
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'v' => $this->params['v'],
            'grant_type' => 'client_credentials'
        );
        $response = file_get_contents(self::OAUTH_URL . self::ACCESS_TOKEN_URL . http_build_query($params));
        $response = json_decode($response, true);
        return $response;
    }

    public function authDirect($client_id, $client_secret, $username, $password, $scope, $test_redirect_uri = 0)
    {
        $params = array(
            'grant_type' => 'password',
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'username' => $username,
            'password' => $password,
            'scope' => $scope,
            'test_redirect_uri' => $test_redirect_uri,
            'v' => $this->params['v']
        );
        $response = file_get_contents(self::OAUTH_URL . self::TOKEN_URL . http_build_query($params));
        $response = json_decode($response, true);

        try {
            if (isset($response['error'])) {
                throw new \Exception($response['error_description']);
            }
        } catch (\Exception $e) {
            echo $response['error'] . ': ' . $e->getMessage();
        }
        return $response;
    }

    public function api($method, $params)
    {
        $params['access_token'] = $this->access_token;
        $params = array_merge($params, $this->params);

        $request_url = '/method/' . $method . $this->format . '?' . http_build_query($params);

        if ($this->secret) {
            $sig = md5($request_url . $this->secret);
            $response = file_get_contents(self::API_URL . $request_url . '&sig=' . $sig);
        } else {
            $response = file_get_contents(self::API_URL . $request_url);
        }

        if (!empty($this->format)) {
            return $response;
        }

        $response = json_decode($response, true);
        try {
            if (isset($response['error'])) {
                throw new \Exception($response['error']['error_msg']);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        return $response;
    }
}
