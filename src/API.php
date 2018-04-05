<?php

namespace AndrewBroberg\PUBG;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use WoohooLabs\Yang\JsonApi\Response\JsonApiResponse;
use WoohooLabs\Yang\JsonApi\Hydrator\ClassHydrator;

class API
{
    private $client;
    private $shard;
    private $apiKey;

    const BASEURI = 'https://api.playbattlegrounds.com/shards/';
    /**
     * Create a new API Instance
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

    private function request($shard, $endpoint, $filters = [])
    {
        $response = $this->client->get("$shard/$endpoint", ['http_errors' => false]);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception($response->getReasonPhrase());
        }

        return $response;
    }

    public function getMatch($shard, $matchId)
    {
        $response = $this->request($shard, "matches/$matchId");
        return $this->processMatchResponse($response);
    }

    public function getPlayer($shard, $playerId)
    {
        $response = $this->request($shard, "players/$playerId");
        return $this->processPlayerResponse($response);
    }

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

    public function setShard($shard)
    {
        $this->shard = $shard;
    }

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

    private function processMatchResponse($matchResponse)
    {
        $response = new JsonApiResponse($matchResponse);
        $hydrator = new ClassHydrator();
        $match = $hydrator->hydrate($response->document());
        return $match;

        $document = $response->document();

        $match = $document->primaryResource();
        $aMatch = new Match($match->idAndAttributes());

        if ($match->hasRelationship('rosters')) {
            $rosters = [];
            foreach ($match->relationship('rosters')->resources() as $resource) {
                $roster = new Roster($resource->idAndAttributes());
                if ($resource->hasRelationship('participants')) {
                    $roster->participants = [];
                    foreach ($resource->relationship('participants')->resources() as $resource) {
                        $roster->participants[] = new Participant($resource->idAndAttributes());
                    }
                }
                $rosters[] = $roster;
            }

            $aMatch->rosters = $rosters;
        }

        return $aMatch;
    }
}
