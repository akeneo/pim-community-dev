<?php

namespace PimEnterprise\Bundle\CatalogBundle\Persistence\ORM;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\CatalogBundle\Persistence\ProductChangesProvider;
use PimEnterprise\Bundle\CatalogBundle\Persistence\Engine\ArrayDiff;

/**
 * Provide changes that happened on a product values
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class EntityChangesProvider implements ProductChangesProvider
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ArrayDiff */
    protected $engine;

    /**
     * @param ManagerRegistry     $registry
     * @param NormalizerInterface $normalizer
     * @param ArrayDiff           $engine
     */
    public function __construct(
        ManagerRegistry $registry,
        NormalizerInterface $normalizer,
        ArrayDiff $engine = null
    ) {
        $this->registry = $registry;
        $this->normalizer = $normalizer;
        $this->engine = $engine ?: new ArrayDiff();
    }

    /**
     * {@inheritdoc}
     */
    public function computeNewValues(ProductInterface $product)
    {
        $manager = $this->registry->getManagerForClass(get_class($product));

        // TODO (2014-05-06 18:28 by Gildas): We should normalize only values
        $current = $this->normalizer->normalize($product, 'csv');

        //FIXME Why do we need to refresh manually the values and values collection data?
        foreach ($product->getValues() as $value) {
            if ($manager->contains($value)) {
                $manager->refresh($value);
            }
            if ($value->getData() instanceof \Doctrine\Common\Collections\Collection) {
                foreach ($value->getData() as $data) {
                    if ($manager->contains($data)) {
                        $manager->refresh($data);
                    }
                }
            }
        }

        $previous = $this->normalizer->normalize($product, 'csv');

        return $this->engine->diff($previous, $current);
    }
}
