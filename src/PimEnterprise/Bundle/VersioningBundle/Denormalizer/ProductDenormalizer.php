<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductDenormalizer extends AbstractEntityDenormalizer
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        var_dump($data); echo "<br />";
        $product = $this->getEntity($data['sku']); //TODO Remove hardcoded stuff

        if (isset($data['family'])) {
            $family = $this->serializer->denormalize($data['family'], 'Pim\Bundle\CatalogBundle\Entity\Family');
            $product->setFamily($family);
        }

        return $product;
    }
}
