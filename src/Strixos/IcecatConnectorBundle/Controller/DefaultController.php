<?php

namespace Strixos\IcecatConnectorBundle\Controller;

use Strixos\IcecatConnectorBundle\Model\SupplierLoader;

use Strixos\IcecatConnectorBundle\Model\ProductLoader;
use Strixos\IcecatConnectorBundle\Model\BaseExtractor;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/icecat")
     * @Template()
     */
    public function indexAction()
    {
        // TODO replace by injection and use loader as services ?
        $entityManager = $this->getDoctrine()->getEntityManager();
        $extractor = new BaseExtractor($entityManager);
        $extractor->process();

/*        $pathSupplierFile = '/tmp/supplier-file.xml';
        $supplierLoader = new SupplierLoader();
        $supplierLoader->updateReferencial($pathSupplierFile);
*/
        /*
        $prodId = 'RJ459AV';
        $vendor = 'hp';
        $locale = 'fr';

        $loader = new ProductLoader();
        $loader->load($prodId, $vendor, $locale);

*/

//        echo $loader->getProductName();

        /*
        $uri    = 'http://data.icecat.biz/xml_s3/xml_server3.cgi';
        $locale = 'fr';
        $vendor = 'hp';
        $prodId = 'RJ459AV';
        $params = array(
            'prod_id'=> $prodId,
            'lang'   => $locale,
            'vendor' => $vendor,
            'output' => 'productxml'
        );*/

//        $request = Request::create($uri, 'GET', $params);

 //       echo $request;
/*
        $uri = 'http://data.Icecat.biz/xml_s3/xml_server3.cgi?prod_id=RJ459AV;vendor=hp;lang=fr;output=productxml';
        $username = 'NicolasDupont';
        $password = '';

        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $uri);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_HEADER, false);
        curl_setopt($c, CURLOPT_USERPWD, $username . ":" . $password);
        $output = curl_exec($c);

        if ($output === false) {
            trigger_error('Erreur curl : '.curl_error($c),E_USER_WARNING);
        } else {
            var_dump($output);
        }
        curl_close($c);
*/


/*
create(string $uri, string $method = 'GET', array $parameters = array(), array $cookies = array(), array $files = array(), array $server = array(), string $content = null)

        $webClient = new \Zend\Http\Client();
        $webClient->setUri($dataUrl);
        $webClient->setMethod(Zend_Http_Client::GET);
        $webClient->setHeaders('Content-Type: text/xml; charset=UTF-8');
        $webClient->setParameterGet($getParamsArray);

        $response = $webClient->request();

        if ($response->isError()) {
            echo 'Response Status: '.$response->getStatus()." Response Message: ".$response->getMessage();
            return false;
        }
        $resultString = $response->getBody();
        echo $resultString;
        */

        return array('name' => 'toto');
    }
}
