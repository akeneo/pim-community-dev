<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Filter;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Product Value filter
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ProductValueAttributeGroupRightFilter extends AbstractAuthorizationFilter implements
    CollectionFilterInterface,
    ObjectFilterInterface
{
    /** @var array */
    protected $attributeGroupAccess = [];

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        parent::__construct($tokenStorage, $authorizationChecker);

        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function filterCollection($collection, $type, array $options = [])
    {
        foreach ($collection as $productValue) {
            if ($this->filterObject($productValue, $type, $options)) {
                $collection->remove($productValue);
            }
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function filterObject($value, $type, array $options = [])
    {
        if (!$this->supportsObject($value, $type, $options)) {
            throw new \LogicException('This filter only handles objects of type "ValueInterface"');
        }

        $attribute  = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());

        if ($attribute !== null) {
            $groupId = $attribute->getGroup()->getId();

            if (!isset($this->attributeGroupAccess[$groupId])) {
                $this->attributeGroupAccess[$groupId] = $this->authorizationChecker->isGranted(
                    Attributes::VIEW_ATTRIBUTES,
                    $attribute->getGroup()
                );
            }

            return !$this->attributeGroupAccess[$groupId];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCollection($collection, $type, array $options = [])
    {
        return $collection instanceof WriteValueCollection && null !== $this->tokenStorage->getToken();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return parent::supportsObject($options, $type, $options) && $object instanceof ValueInterface;
    }
}
