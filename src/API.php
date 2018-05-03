<?php

namespace AndrewBroberg\PUBG;

use Exception;
use GuzzleHttp\Client;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use WoohooLabs\Yang\JsonApi\Response\JsonApiResponse;
use WoohooLabs\Yang\JsonApi\Hydrator\ClassHydrator;

class API
{
    private $client;
    private $shard;

    const BASEURI = 'https://api.playbattlegrounds.com/shards/';
    private $apiKey;

    /**
     * Create a new API Instance
     * @param $apiKey
     */
    public function __construct($apiKey)
    {
        $this->client = new Client([
            'base_uri' => self::BASEURI,
            'headers' => [
                'Authorization' => "Bearer $apiKey",
                'Accept' => 'application/vnd.api+json'
            ]
        ]);
    }

    /**
     * @param $shard
     * @param $endpoint
     * @return \Psr\Http\Message\ResponseInterface
     * @throws Exception
     */
    private function request($shard, $endpoint)
    {
        $response = $this->client->get("$shard/$endpoint", ['http_errors' => false]);

        $statusCode = $response->getStatusCode();

        if ($statusCode == 404) {
            throw new ResourceNotFoundException();
        }

        if ($statusCode == 429) {
            throw new TooManyRequestsException(
                $response->getHeaderLine('x-ratelimit-limit'),
                $response->getHeaderLine('x-ratelimit-remaining'),
                $response->getHeaderLine('x-ratelimit-reset')
            );
        }

        if ($statusCode == 415) {
            throw new InvalidContentTypeException();
        }

        if ($statusCode == 401) {
            throw new InvalidAPIKeyException();
        }

        return $response;
    }

    /**
     * @param $telemetryUrl
     * @return mixed
     * @throws Exception
     */
    public function getTelemetry($telemetryUrl)
    {
        $response = $this->client->get($telemetryUrl);

        if ($response->getStatusCode() !== 200) {
            throw new Exception($response->getReasonPhrase());
        }

        return json_decode($response->getBody()->getContents());

    }

    /**
     * @param $shard
     * @param $matchId
     * @return array|\stdClass
     * @throws Exception
     */
    public function getMatch($shard, $matchId)
    {
        $response = $this->request($shard, "matches/$matchId");
        return $this->processMatchResponse($response);
    }

    /**
     * @param $shard
     * @param $playerId
     * @return array|\stdClass
     * @throws Exception
     */
    public function getPlayer($shard, $playerId)
    {
        $response = $this->request($shard, "players/$playerId");
        return $this->processPlayerResponse($response);
    }

    public function getPlayerSeason($shard, $playerId, $season)
    {
        $response = $this->request($shard, "players/$playerId/seasons/$season");
        $response = new JsonApiResponse($response);
        $hydrator = new ClassHydrator();
        $playerSeason = $hydrator->hydrate($response->document());

        $matches = $response->document()->primaryResource()->relationship('matchesSolo')->toArray();
        $playerSeason->matches['solo'] = [];
        foreach ($matches['data'] as $match) {
            $playerSeason->matches['solo'][] = $match['id'];
        }

        $matches = $response->document()->primaryResource()->relationship('matchesSoloFPP')->toArray();
        $playerSeason->matches['solo-fpp'] = [];
        foreach ($matches['data'] as $match) {
            $playerSeason->matches['solo-fpp'][] = $match['id'];
        }

        $matches = $response->document()->primaryResource()->relationship('matchesDuo')->toArray();
        $playerSeason->matches['duo'] = [];
        foreach ($matches['data'] as $match) {
            $playerSeason->matches['duo'][] = $match['id'];
        }

        $matches = $response->document()->primaryResource()->relationship('matchesDuoFPP')->toArray();
        $playerSeason->matches['duo-fpp'] = [];
        foreach ($matches['data'] as $match) {
            $playerSeason->matches['duo-fpp'][] = $match['id'];
        }

        $matches = $response->document()->primaryResource()->relationship('matchesSquad')->toArray();
        $playerSeason->matches['squad'] = [];
        foreach ($matches['data'] as $match) {
            $playerSeason->matches['squad'][] = $match['id'];
        }

        $matches = $response->document()->primaryResource()->relationship('matchesSquadFPP')->toArray();
        $playerSeason->matches['squad-fpp'] = [];
        foreach ($matches['data'] as $match) {
            $playerSeason->matches['squad-fpp'][] = $match['id'];
        }

        return $playerSeason;
    }

    public function getSeasons($shard)
    {
        $response = $this->request($shard, "seasons");

        $response = new JsonApiResponse($response);
        $hydrator = new ClassHydrator();
        $seasons = $hydrator->hydrate($response->document());

        return $seasons;
    }

    /**
     * @param $shard
     * @param array $filters
     * @return array|\stdClass
     * @throws Exception
     */
    public function getPlayers($shard, array $filters)
    {
        $filters = $this->buildFilters($filters);
        $response = $this->request($shard, "players?$filters");

        return $this->processPlayersResponse($response);
    }

    /**
     * [buildFilters description]
     * @param  [type] $filters [description]
     * @return [type]          [description]
     */
    private function buildFilters($filters)
    {
        foreach ($filters as &$filter) {
            if (is_array($filter)) {
                $filter = implode(',', $filter);
            }
        }

        return http_build_query(['filter' => $filters]);
    }

    /**
     * @param $shard
     */
    public function setShard($shard)
    {
        $this->shard = $shard;
    }

    /**
     * @param $response
     * @return array|\stdClass
     */
    private function processPlayerResponse($response)
    {
        $response = new JsonApiResponse($response);
        $hydrator = new ClassHydrator();
        $player = $hydrator->hydrate($response->document());

        $matches = $response->document()->primaryResource()->relationship('matches')->toArray();
        $player->matches = [];
        foreach ($matches['data'] as $match) {
            $player->matches[] = $match['id'];
        }

        return $player;
    }

    /**
     * @param $response
     * @return array|\stdClass
     */
    private function processPlayersResponse($response)
    {
        $response = new JsonApiResponse($response);
        $hydrator = new ClassHydrator();
        $players = $hydrator->hydrate($response->document());

        foreach ($players as $player) {
            $player->matches = [];
            foreach ($response->document()->primaryResources() as $playerResource) {
                if ($playerResource->id() == $player->id) {
                    $matches = $playerResource->relationship('matches')->toArray();
                    foreach ($matches['data'] as $match) {
                        $player->matches[] = $match['id'];
                    }
                }
            }
        }

        return $players;
    }

    /**
     * @param $matchResponse
     * @return array|\stdClass
     */
    private function processMatchResponse($matchResponse)
    {
        $response = new JsonApiResponse($matchResponse);
        $hydrator = new ClassHydrator();
        $match = $hydrator->hydrate($response->document());
        return $match;
    }
}
