<?php
/**
 * @package stopforumspam
 * @subpackage spam
 */

use Exception;
use SimpleXMLElement;
use MODX\Revolution\modX;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;


class StopForumSpam {

    function __construct(modX &$modx, array $config = array()) {
        $this->modx =& $modx;

        $this->config = array_merge(array(
            'host' => 'http://www.stopforumspam.com/',
            'path' => 'api',
            'method' => 'GET',
        ),$config);
    }

    /**
     * Check for spammer
     *
     * @access public
     * @param string $ip
     * @param string $email
     * @param string $username
     * @return array An array of errors
     */
    public function check($ip = '',$email = '',$username = '') {
        $params = array();
        if (!empty($ip)) {
            if (in_array($ip,array('127.0.0.1','::1','0.0.0.0'))) $ip = '72.179.10.158';
            $params['ip'] = $ip;
        }
        if (!empty($email)) $params['email'] = $email;
        if (!empty($username)) $params['username'] = $username;

        $xml = $this->request($params);
        $i = 0;
        $errors = array();
        foreach ($xml->appears as $result) {
            if ($result == 'yes') {
                $errors[] = ucfirst($xml->type[$i]);
            }
            $i++;
        }
        return $errors;
    }

    /**
     * Make a request to stopforumspam.com
     *
     * @access public
     * @param array $params An array of parameters to send
     * @return mixed The return SimpleXML object, or false if none
     */
    public function request($params = array()) {
        $client = $this->modx->services->get(ClientInterface::class);
        $factory = $this->modx->services->get(RequestFactoryInterface::class);

         $uri = $this->config['host'] . $this->config['path'];
        if (strtoupper($this->config['method']) == 'GET') {
            $uri .= (strpos($uri, '?') > 0) ? '&' : '?';
            $uri .= http_build_query($params);
        }

        $request = $factory->createRequest($this->config['method'], $uri);

        if (strtoupper($this->config['method']) == 'POST') {
            $request->getBody()->write(json_encode($params));
        }

        try {
            $response = $client->sendRequest($request)->withHeader('Accept', 'text/xml');
        } catch (ClientExceptionInterface $e) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[StopForumSpam] Could not load response from: ' . $this->config['host']);
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[StopForumSpam] Error: ' . $e->getMessage());
            return true;
        }

        $responseXml = $this->toXml($response->getBody()->getContents());

        return $responseXml;
    }

    /**
     *  Interprets the response string of XML into an object
     *
     * @return SimpleXMLElement
     */
    private function toXml($response) {
        $xml = null;

        try {
            $xml = simplexml_load_string($response);
        } catch (Exception $e) {
            $this->modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not parse XML response from provider: ' . $response);
        }
        if (!$xml) {
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><error><message>' . $this->modx->lexicon('provider_err_blank_response') . '</message></error>');
        }
        return $xml;
    }
}
