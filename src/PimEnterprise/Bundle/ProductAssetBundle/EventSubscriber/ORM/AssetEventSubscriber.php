<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\EventSubscriber\ORM;

use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent;
use PimEnterprise\Bundle\WorkflowBundle\Exception\PublishedProductConsistencyException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Asset event subscriber for ORM
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AssetEventSubscriber implements EventSubscriberInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param AttributeRepositoryInterface        $attributeRepository
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->pqbFactory          = $pqbFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AssetEvent::PRE_REMOVE => 'isAssetRemovable'
        ];
    }

    /**
     * Checks if the asset is not used in a published product
     *
     * @param GenericEvent $event
     *
     * @throws PublishedProductConsistencyException
     *
     * @return GenericEvent
     */
    public function isAssetRemovable(GenericEvent $event)
    {
        $asset          = $event->getSubject();
        $attributeCodes = $this->attributeRepository->getAttributeCodesByType('pim_assets_collection');

        foreach ($attributeCodes as $attributeCode) {
            $ppqb = $this->pqbFactory->create();
            $publishedProducts = $ppqb
                ->addFilter($attributeCode, 'IN', [$asset->getId()])
                ->execute();

            if ($publishedProducts->count() > 0) {
                throw new PublishedProductConsistencyException(
                    'Impossible to remove an asset linked to a published product'
                );
            }
        }

        return $event;
    }
}
