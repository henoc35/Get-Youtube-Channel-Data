<?php

namespace Cores;
/*
 * PHP Class to Retrieve All Videos from a YouTube Channel
 * @author Henoc Djabia <henoc35@gmail.com>
 * @copyright 2017
 * Category
 */

class Youtube
{
    private $devKey;
    //private $secretKey;
    private $youtubeService;
    private $nextPageToken;
    private $prevPageToken;

    /**
     * Youtube constructor.
     * @param string $dev
     * @internal param null|string $secret
     */
    public function __construct(string $dev/*, ?string $secret = null*/)
    {
        $this->devKey = $dev;
        //$this->secretKey = $secret;
        $client = new \Google_Client();
        $client->setDeveloperKey($this->devKey);
        $this->youtubeService = new \Google_Service_YouTube($client);
    }

    /**
     * @param $part
     * @param $params
     * @param null|string $pageToken
     * @return array
     */
    public function getVideosByChanelId($part, $params, ?string $pageToken = null): array
    {
        $params = array_filter($params);
        $responses = $this->youtubeService->channels->listChannels(
            $part,
            $params
        );
        if (!is_null($pageToken)){
            foreach ($responses->items as $its)
            {
                $d = $its->contentDetails->relatedPlaylists->uploads;
                $playlistItemsResponse = $this->youtubeService->playlistItems->listPlaylistItems('snippet', array(
                    'playlistId' => $d,
                    'pageToken' => $pageToken,
                    'maxResults' => 5
                ));

                $this->setPrevPageToken($playlistItemsResponse->getPrevPageToken());
                $this->setNextPageToken($playlistItemsResponse->getNextPageToken());
                $result = [];
                foreach ($playlistItemsResponse->items as $playlistItem){
                    $result[] = $playlistItem;
                }
            }
        }else{
            foreach ($responses->items as $its)
            {
                $d = $its->contentDetails->relatedPlaylists->uploads;
                $playlistItemsResponse = $this->youtubeService->playlistItems->listPlaylistItems('snippet', array(
                    'playlistId' => $d,
                    'maxResults' => 5
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
    public function getPublishAt(string $date): string
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


}