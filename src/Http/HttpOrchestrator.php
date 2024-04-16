<?php

namespace GrupoCometa\ClientOrchestrator\Http;

use GuzzleHttp\Client;

class HttpOrchestrator
{

    private $http;
    private $sso;

    public function __construct()
    {
        $this->http = new Client([
            'base_uri' => config('orchestrator.orchestratorUrl'),
            'headers' => [
                'content-type' => 'application/json'
            ]
        ]);

        $this->sso = new Sso;
    }

    public function getRobotIdByPublicId($publicId)
    {
        $token = $this->sso->getAccessToken();
        $response = $this->http->get("robots?public_id=$publicId", [
            'headers' => [
                'Authorization' => "Bearer $token",
                'content-type' => 'application/json'
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        
        return $data['data'][0]['id'];
    }

    public function resendSchedules($robotId)
    {
        $token = $this->sso->getAccessToken();
        return $this->http->post("robots/$robotId/resend-schedules", [
            'headers' => [
                'Authorization' => "Bearer $token",
                'content-type' => 'application/json'
            ]
        ]);
    }
}
