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
    
    protected $allowedMathods = array(
        'POST' => HTTP_METH_POST, 
        'GET' => HTTP_METH_GET,
        'PUT' => HTTP_METH_PUT,
        'DELETE' => HTTP_METH_DELETE,
        );
  
    
    /**
     * @Assert\NotBlank
     * @Assert\Url
     */
    protected $url;
    
    
    protected $method = 'POST';

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
     * @return string
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get the $method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set the method
     *
     * @return string
     */
    public function setMethod($method)
    {
        $this->method = $method;
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
            $this->incrementCount();
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

}
