<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;


use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Description of ObjectAttributesNormalizer
 *
 * @author wn-s.rascar
 */
class ObjectAttributesNormalizer implements NormalizerInterface
{
    
    
    /**
     * @var array
     */
    protected $supportedFormats = array('json');
    /**
     * @var array
     */
    protected $ignoredAttributes = array();
    /**
     * @var array
     */
    protected $callbacks = array();
    
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $reflectionObject = new \ReflectionObject($object);
        $reflectionAttributes = $reflectionObject->getProperties(\ReflectionMethod::IS_PUBLIC);
        $returnedArray = array();
        foreach ($reflectionAttributes as $attribute) {

            $attributeName = $attribute->name;

            if (in_array($attributeName, $this->ignoredAttributes)) {
                continue;
            }

            $attributeValue = $object->$attributeName;
            if (array_key_exists($attributeName, $this->callbacks)) {
                $attributeValue = call_user_func($this->callbacks[$attributeName], $attributeValue);
            }

            $returnedArray[$attributeName] = $attributeValue;
            
        }

        return $returnedArray;
    }

   /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_object($data) && in_array($format, $this->supportedFormats);
    }

}
