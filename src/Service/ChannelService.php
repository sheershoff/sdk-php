<?php

namespace Pact\Service;

use DateTimeInterface;
use Pact\Exception\InvalidArgumentException;
use Pact\Http\Methods;
use Pact\Service\AbstractService;

class ChannelService extends AbstractService
{
    protected static string $endpoint = 'companies/%s/channels';

    /**
     * @param array Route parameters validation method
     * @throws InvalidArgumentException
     */
    protected function validateRouteParams($params)
    {
        [$companyId] = $params;
        $this->validator->_($companyId<0, 'Id of company must be greater or equal than 0');
    }

    /**
     * This method returns all the company channels.
     * @link https://pact-im.github.io/api-doc/#get-all-channels
     * 
     * @param int $companyId Id of the company
     * @param string $from Next page token geted from last request. Not valid or empty token return first page
     * @param int $per Number of elements per page. Default: 50
     * @param string $sort Change sorting direction (sorting by id). Avilable values: asc, desc. Default: asc.
     */
    public function getChannels(int $companyId, string $from = null, int $per = null, string $sort = null) 
    {
        $this->validator->_(strlen($from)>255, 'Parameter 2 must be length less or equal 255');
        $this->validator->between($per, 1, 100);
        $this->validator->sort($sort);

        $query = [
            'from' => $from,
            'per' => $per,
            'sort_direction' => $sort
        ];
        return $this->request(Methods::GET, static::$endpoint, [$companyId], $query);
    }

    /**
     * Unified method that can create channel in company.
     * @link https://pact-im.github.io/api-doc/#create-new-channel
     * @note You can connect only one channel per one company for each provider. 
     *       Contact with support if you want to use more than one channel
     * 
     * @param int $companyId Id of the company
     * @param string $provider
     * @param array $parameters
     */
    public function createChannelUnified(int $companyId, string $provider, array $parameters = [])
    {
        $body = array_merge(
            ['provider' => $provider],
            $parameters
        );
        return $this->request(Methods::POST, static::$endpoint, [$companyId], [], [], $body);
    }

    /**
     * This method create a new channel in the company using token.
     * @link https://pact-im.github.io/api-doc/#create-new-channel
     * @note List of supported channels that can be created by token
     *       you can see in link above
     * 
     * @param int $companyId Id of the company
     * @param string $provider (facebook, viber, vk, ...)
     * @param string $token
     * @return Json|null
     */
    public function createChannelByToken(int $companyId, string $provider, string $token)
    {
        $this->validator->_(strlen($token)==0, 'Token must not be empty string');
        $this->createChannelUnified($companyId, $provider, ['token' => $token]);
    }

    /**
     * This method create a new channel for WhatsApp
     * @link https://pact-im.github.io/api-doc/#create-new-channel
     * 
     * @param int $companyId Id of the company
     * @param DateTimeInterface $syncMessagesFrom Only messages created after will be synchronized
     * @param bool $doNotMarkAsRead Do not mark chats as read after synchronization
     * @return Json|null
     */
    public function createChannelWhatsApp(
        int $companyId, 
        DateTimeInterface $syncMessagesFrom = null, 
        bool $doNotMarkAsRead = null
    ) {
        $body = [
            'sync_messages_from' => $syncMessagesFrom->getTimestamp(),
            'do_not_mark_as_read' => $doNotMarkAsRead
        ];
        return $this->createChannelUnified($companyId, 'whatsapp', $body);
    }

    /**
     * This method create a new channel for WhatsApp
     * @link https://pact-im.github.io/api-doc/#create-new-channel
     * 
     * @param int $companyId Id of the company
     * @param string $login Instagram login
     * @param string $password Instagram passowrd
     * @param DateTimeInterface $syncMessagesFrom Only messages created after will be synchronized
     * @param bool $syncComments
     * @return Json|null
     */
    public function createChannelInstagram(
        int $companyId, 
        string $login,
        string $password,
        DateTimeInterface $syncMessagesFrom = null,
        bool $syncComments = null
    ) {
        $body = [
            'login' => $login,
            'password' => $password,
            'sync_messages_from' => $syncMessagesFrom->getTimestamp(),
            'sync_comments' => $syncComments
        ];
        return $this->createChannelUnified($companyId, 'instagram', $body);
    }

    /**
     * This method updates existing channel in the company
     * @link https://pact-im.github.io/api-doc/#update-channel
     * 
     * @param int $companyId 
     * @param int $conversationId 
     * @param array $parameters
     * @return Json|null
     */
    public function updateChannel(int $companyId, int $conversationId, array $parameters = [])
    {
        $this->validator->_($conversationId<0, 'Id of conversation must be greater or equal than 0');
        return $this->request(
            Methods::PUT, 
            static::$endpoint . '/%s', 
            [$companyId, $conversationId],
            [],
            [],
            $parameters
        );
    }

    /**
     * This method updates instagramm channel
     * @link https://pact-im.github.io/api-doc/#update-channel
     * 
     * @param int $companyId 
     * @param int $conversationId 
     * @param string $login Instagram login
     * @param string $password Instagram password
     * @return Json|null
     */
    public function updateChannelInstagram(
        int $companyId,
        int $conversationId,
        string $login,
        string $password
    ) {
        $body = [
            'login' => $login,
            'password' => $password
        ];
        $this->updateChannel($companyId, $conversationId, $body);
    }

    /**
     * This method updates channels that using tokens to auth
     * @link https://pact-im.github.io/api-doc/#update-channel
     * @note List of supported channels that can be created by token
     *       you can see in link above
     * 
     * @param int $companyId 
     * @param int $conversationId 
     * @param string $token
     * @return Json|null
     */
    public function updateChannelToken(int $companyId, int $conversationId, string $token)
    {
        $this->validator->_(strlen($token)==0, 'Token must not be empty string');
        $this->updateChannel($companyId, $conversationId, ['token'=>$token]);
    }
}