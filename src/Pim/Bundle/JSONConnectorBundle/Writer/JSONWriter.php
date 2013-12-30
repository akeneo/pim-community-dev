<?php

namespace Pim\Bundle\JSONConnectorBundle\Writer;

use Oro\Bundle\BatchBundle\Item\ItemWriterInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class JSONWriter description
 * 
 * @copyright 2014 Sylvain Rascar <srascar@webnet.fr>
 * @author Sylvain Rascar <srascar@webnet.fr>
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JSONWriter extends AbstractConfigurableStepElement implements
    ItemWriterInterface,
    StepExecutionAwareInterface
{
    
    const JSON_HEADERS_CONTENT_TYPE = 'application/json';
  
    
    /**
     * @Assert\NotBlank
     * @Assert\Url
     */
    protected $url;
    

    /**
     * Get the url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the url
     *
     * @param string $url
     * 
     * @return JSONWriter
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $request = $this->generateHttpRequest($items);
        $response = $request->send();
        if( $this->handleResponse($response)){
            $this->incrementCount($items);
        }
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'url' => array(
                'options' => array(
                    'required' => true
                )),
        );
    }
    
    /**
     * generate a post resquest which contain products in json
     * 
     * @param array $items
     * 
     * @return \HttpRequest
     */
    protected function generateHttpRequest($items)
    {

        $request = new \HttpRequest($this->url);
        $request->setBody($this->generateBody($items));
        $request->setMethod(HTTP_METH_POST);
        $request->addHeaders(array(
            'Content-type' => self::JSON_HEADERS_CONTENT_TYPE,
        ));
       
        return $request;
    }
    
    /**
     * generate the post body 
     * 
     * @param array $items
     * 
     * @return \HttpRequest
     */  
    protected function generateBody($items)
    {
        return json_encode($items);
    }

    
    /**
     * Handle response object
     * Extends JSONWriter and override this method
     * to set your own logical
     * 
     * @param \HttpMessage $response
     * 
     * @return boolean
     */      
    protected function handleResponse(\HttpMessage $response)
    {
        if ($response->getResponseCode() !== 200){
            throw new \Exception("Server reponse error. \n".
                "Code : ".$response->getResponseCode()."\n".
                "Message : ".$response->getBody()
                );
        }

        return $response->getResponseCode() == 200;
    }

    
    /**
     * count the number of entry sent 
     * 
     * @param array $items
     * 
     * @return
     */      
    protected function incrementCount($items)
    {
        foreach ($items as $item) {
            $this->stepExecution->incrementSummaryInfo('write');
        }
    }
}
