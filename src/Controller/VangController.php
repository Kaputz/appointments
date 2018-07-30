<?php
/**
 * Vanguarda: Soluções de Gestão, Lda.
 * 
 * (c) Hugo Alvela <hugo.alvela@vanguarda.pt>
 * 
 */
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Translation\TranslatorInterface;

class VangController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index() 
    {

        /* $url = 'http://vanguarda.ddns.net:444/sfa_suporte_files/api.asmx?WSDL';
        $method = 'HelloWorld';
        $params = array();
        $client = new \SoapClient($url, array('cache_wsdl' => WSDL_CACHE_NONE));

        $new_str=json_encode($client->__getFunctions());
        $new_str.=json_encode($client->__getTypes());
        //$new_str=$client->HelloWorld();

        file_put_contents("/var/www/html/log.txt", "\n" . $new_str, FILE_APPEND | LOCK_EX); */

        return $this->redirectToRoute(
            'appointment'
        );

        //return $this->render('vang/index.html.twig');
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function admin()
    {
        return new Response('<html><body>Admin page!</body></html>');
    }

}
