<?php
namespace Pim\Bundle\ImportExportBundle\Processor\JSONNormalizer;

use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Productnormalizer
 *
 * @author wn-s.rascar
 */
class JSONNormalizer extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    
    protected $normalizer;


    /**
     * @param NormalizerInterface $normalizer
     */
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }
    
    public function process($items)
    {
        $itemsNormalized = array();
        foreach ($items as $items){
            $itemsNormalized[] = $this->normalizer->normalize($items);
        }
        return $itemsNormalized;
    }

    public function getConfigurationFields()
    {
        return array();
    }

//put your code here
}
