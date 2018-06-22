<?php
declare(strict_types=1);

namespace PimEnterprise\Bundle\SecurityBundle\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use PimEnterprise\Component\Security\Attributes;

/**
 * Products and product models delete right filter.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ProductAndProductModelDeleteRightFilter extends AbstractAuthorizationFilter implements
    CollectionFilterInterface,
    ObjectFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filterObject($entity, $type, array $options = [])
    {
        if (!$this->supportsObject($entity, $type, $options)) {
            throw new \LogicException(
                'This filter only handles objects of type "ProductInterface" and "ProductModelInterface"'
            );
        }

        return !$this->authorizationChecker->isGranted(Attributes::OWN, $entity);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return parent::supportsObject($options, $type, $options) &&
            ($object instanceof ProductInterface || $object instanceof ProductModelInterface);
    }
}
