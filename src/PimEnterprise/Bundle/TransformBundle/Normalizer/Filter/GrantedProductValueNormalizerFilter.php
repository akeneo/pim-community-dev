<?php

namespace PimEnterprise\Bundle\TransformBundle\Normalizer\Filter;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\TransformBundle\Normalizer\Filter\NormalizerFilterInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Filter the granted product value objects.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class GrantedProductValueNormalizerFilter implements NormalizerFilterInterface
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /**
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(Collection $objects, array $context = [])
    {
        $objects = $objects->filter(
            function ($value) {
                if (!$value instanceof ProductValueInterface) {
                    throw new \Exception('This filter only handles objects of type "ProductValueInterface"');
                }
                $attributeGroup = $value->getAttribute()->getGroup();

                return $this->securityContext->isGranted(Attributes::VIEW_ATTRIBUTES, $attributeGroup);
            }
        );

        return $objects;
    }
}
