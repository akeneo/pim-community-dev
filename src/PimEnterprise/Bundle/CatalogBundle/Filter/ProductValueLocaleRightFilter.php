<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Filter;

use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Product Value filter
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ProductValueLocaleRightFilter extends AbstractAuthorizationFilter implements CollectionFilterInterface, ObjectFilterInterface
{
    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param TokenStorageInterface         $tokenStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param LocaleRepositoryInterface     $localeRepository
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        LocaleRepositoryInterface $localeRepository
    ) {
        parent::__construct($tokenStorage, $authorizationChecker);

        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function filterObject($productValue, $type, array $options = [])
    {
        if (!$this->supportsObject($productValue, $type, $options)) {
            throw new \LogicException('This filter only handles objects of type "ProductValueInterface"');
        }

        return $productValue->getAttribute()->isLocalizable() &&
            !$this->authorizationChecker->isGranted(
                Attributes::VIEW_ITEMS,
                $this->localeRepository->findOneByIdentifier($productValue->getLocale())
            );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return parent::supportsObject($options, $type, $options) && $object instanceof ProductValueInterface;
    }
}
