<?php

namespace PimEnterprise\Bundle\CatalogRuleBundle\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ProductRuleDenormalizer implements DenormalizerInterface
{
    /** @var string */
    protected $class;

    /** @var Product */
    protected $contentSerializer;

    /**
     * @param $class
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * Denormalizes data back into an object of the given class
     *
     * @param mixed  $data data to restore
     * @param string $class the expected class to instantiate
     * @param string $format format the given data was extracted from
     * @param array  $context options available to the denormalizer
     *
     * @return object
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {




    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $this->class === $type;
    }

}
