<?php
/**
 * Vanguarda: Soluções de Gestão, Lda.
 * 
 * (c) Hugo Alvela <hugo.alvela@vanguarda.pt>
 * 
 */
namespace App\Controller;

use GuzzleHttp\Client;

class HttpService
{
    /**
     * Perform HTTP Request (AJAX)
     * 
     * @param $siteUrl
     * @param $method, default 'GET, accepts 'POST' aswell.
     * @param $data, default NULL
     * @return $response
     */
    public function performRequest($siteUrl, $method = 'GET', $data = null)
    {
        $client = new Client([
            'base_uri' => $siteUrl,
            'timeout'  => 20.0,
        ]);

        $response = $client->get('');

        /* log 
        $new_str = 
            "\n"."code: ".$response->getStatusCode().
            "\n"."reason : ".$response->getReasonPhrase()."\n";
        foreach ($response->getHeaders() as $name => $values) {
            $new_str .= $name . ': ' . implode(', ', $values) . "\r\n";
        }
        $new_str .="\n".$response->getBody();
        file_put_contents("/var/www/html/log.txt", "\n" . $new_str, FILE_APPEND | LOCK_EX);*/

        
        /* \Psr\Http\Message\ResponseInterface */
        return $response;
    }
}
