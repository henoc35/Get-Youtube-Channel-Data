<?php

namespace Cores;
/*
 * PHP Class to Retrieve All Videos from a YouTube Channel
 * @author Henoc Djabia <henoc35@gmail.com>
 * @copyright 2017
 */

class Youtube
{
    private $devKey;
    private $youtubeService;
    private $nextPageToken;
    private $prevPageToken;
    private $client;

    public function __construct()
    {
        $this->client = new \Google_Client();
    }

    /**
     * @param string $apiKey
     */
    public function apiKey(string $apiKey)
    {
        $this->devKey = $apiKey;
    }

    /**
     * @return \Google_Service_YouTube
     */
    private function youtubeService(): \Google_Service_YouTube
    {
        $this->client->setDeveloperKey($this->devKey);
        return $this->youtubeService = new \Google_Service_YouTube($this->client);
    }


    /**
     * @param string $part
     * @param array $params
     * @return \Google_Service_YouTube_ChannelListResponse
     */
    private function channelResponses(string $part, array $params): \Google_Service_YouTube_ChannelListResponse
    {
        return $responses = $this->youtubeService()->channels->listChannels(
            $part,
            $params
        );
    }

    /**
     * @param $part
     * @param $params
     * @param null|string $pageToken
     * @param int $maxResults
     * @return array
     */
    public function getVideosByChanelId(string $part, array $params, ?string $pageToken = null, int $maxResults = 9): array
    {
        $params = array_filter($params);
        if (!is_null($pageToken)){
            foreach ($this->channelResponses($part, $params)->items as $its)
            {
                $d = $its->contentDetails->relatedPlaylists->uploads;
                $playlistItemsResponse = $this->youtubeService()->playlistItems->listPlaylistItems('snippet', array(
                    'playlistId' => $d,
                    'pageToken' => $pageToken,
                    'maxResults' => $maxResults
                ));

                $this->setPrevPageToken($playlistItemsResponse->getPrevPageToken());
                $this->setNextPageToken($playlistItemsResponse->getNextPageToken());
                $result = [];
                foreach ($playlistItemsResponse->items as $playlistItem){
                    $result[] = $playlistItem;
                }
            }
        }else{
            foreach ($this->channelResponses($part, $params)->items as $its)
            {
                $d = $its->contentDetails->relatedPlaylists->uploads;
                $playlistItemsResponse = $this->youtubeService()->playlistItems->listPlaylistItems('snippet', array(
                    'playlistId' => $d,
                    'maxResults' => $maxResults
                ));

                $this->setPrevPageToken($playlistItemsResponse->getPrevPageToken());
                $this->setNextPageToken($playlistItemsResponse->getNextPageToken());
                $result = [];
                foreach ($playlistItemsResponse->items as $playlistItem){
                    $result[] = $playlistItem;
                }
            }
        }
        return $result;
    }

    /**
     * @param string $date
     * @return string
     */
    public static function getPublishAt(string $date): string
   {
       $timezone = NULL;
       $resul = new \DateTime($date, $timezone);
       setlocale(LC_ALL, 'fr');
       $date2 = strftime("%d %B, %Y", $resul->getTimestamp());
       return $date2;
   }

    /**
     * @return mixed
     */
    public function getNextPageToken()
    {
        return $this->nextPageToken;
    }

    /**
     * @return mixed
     */
    public function getPrevPageToken()
    {
        return $this->prevPageToken;
    }

    /**
     * @param mixed $prevPageToken
     */
    private function setPrevPageToken($prevPageToken)
    {
        $this->prevPageToken = $prevPageToken;
    }

    /**
     * @param mixed $nextPageToken
     */
    private function setNextPageToken($nextPageToken)
    {
        $this->nextPageToken = $nextPageToken;
    }

    /**
     * @param string $part
     * @param array $params
     * @return \Google_Service_YouTube_PlaylistItemSnippet
     */
    public function getLastVideoFromChannel(string $part, array $params): \Google_Service_YouTube_PlaylistItemSnippet
    {
        $params = array_filter($params);
        foreach ($this->channelResponses($part, $params)->getItems() as $item){
            $uploads = $item->contentDetails->relatedPlaylists->uploads;
            $playlistItemsResponse = $this->youtubeService()->playlistItems->listPlaylistItems('snippet', array(
                'playlistId' => $uploads
            ));
            return $playlistItemsResponse->getItems()[0]->snippet;
        }
    }

}