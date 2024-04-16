<?php

namespace GrupoCometa\ClientOrchestrator\Http;

use GuzzleHttp\Client;

class Sso
{
    private $http;
    private $config;

    public function __construct()
    {
        $this->config = config('orchestrator');

        $this->http = new Client([
            'base_uri' => $this->config['ssoUrl'],
            'headers' => [
                'content-type' => 'application/x-www-form-urlencoded'
            ]
        ]);
    }

    public function getAccessToken()
    {
        $response = $this->http->post('realms/GC/protocol/openid-connect/token', [
            'form_params' => [
                'username' => $this->config['ssoUsername'],
                'password' => $this->config['ssoPassword'],
                'grant_type' => 'password',
                'client_id' => $this->config['ssoClientId']
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        return $data['access_token'];
    }
}
