<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Persistence;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Persistence\Engine\ArrayDiff;

/**
 * Provide changes that happened on a product values
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductChangesProvider
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
     * Compute a product changes
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    public function computeChanges(ProductInterface $product)
    {
        $manager = $this->registry->getManagerForClass(get_class($product));

        $current = $this->normalizer->normalize($product, 'proposal');

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

        $previous = $this->normalizer->normalize($product, 'proposal');

        return $this->engine->diff($previous, $current);
    }
}
