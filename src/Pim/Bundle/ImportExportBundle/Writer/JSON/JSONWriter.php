<?php

namespace Pim\Bundle\ImportExportBundle\Writer\JSON;

use Symfony\Component\Validator\Constraints as Assert;
use Oro\Bundle\BatchBundle\Item\ItemWriterInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Oro\Bundle\BatchBundle\Entity\StepExecution;

/**
 * Description of JSONPoster
 *
 * @author wn-s.rascar
 */
class JSONWriter extends AbstractConfigurableStepElement implements 
        ItemWriterInterface, 
        StepExecutionAwareInterface
{

    const JSON_HEADERS_CONTENT_TYPE = 'application/json';

    /**
     * @Assert\NotBlank
     */
    protected $url;

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function write(array $items)
    {
        $request = $this->generateHttpRequest($items);
        $response = $request->send();
        return $this->handleResponse($response);
    }

    public function getConfigurationFields()
    {
        return array(
            'url' => array(),
        );
    }

    protected function generateHttpRequest($items)
    {

        $request = new \HttpRequest($this->url);
        $request->setBody($this->generateBody($items));
        $request->setMethod(HTTP_METH_POST);
        $request->addHeaders(array(
            'Content-type' => self::JSON_HEADERS_CONTENT_TYPE,
        ));
        
//        var_dump($request->getHeaders());
//        var_dump($request->getBody());
        return $request;
    }

    protected function generateBody($items)
    {
        $formatedItems = array();
        foreach ($items as $item){
            $formatedItems = array_merge($formatedItems, $item);
        }
        return json_encode($formatedItems);
    }

    protected function handleResponse(\HttpMessage $response)
    {
        if ($response->getResponseCode() !== 200){
            throw new \Exception("Server reponse error. \n".
                    "Code : ".$response->getResponseCode()."\n".
                    "Message : ".$response->getBody()
                    );
        }
        var_dump($response->getResponseCode());
        var_dump($response->getBody());
        return array(
            'code' => $response->getResponseCode(),
            'body' => $response->getBody(),
        );
    }


    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
    
    
}
