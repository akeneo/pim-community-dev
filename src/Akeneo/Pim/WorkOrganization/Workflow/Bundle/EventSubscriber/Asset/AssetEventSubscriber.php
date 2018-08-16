<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\Asset;

use Akeneo\Asset\Bundle\AttributeType\AttributeTypes;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Exception\PublishedProductConsistencyException;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
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
        $this->pqbFactory = $pqbFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_REMOVE => 'checkPublishedProductConsistency',
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
    public function checkPublishedProductConsistency(GenericEvent $event)
    {
        $asset = $event->getSubject();

        if ($asset instanceof AssetInterface) {
            $attributeCodes = $this->attributeRepository->getAttributeCodesByType(AttributeTypes::ASSETS_COLLECTION);

            foreach ($attributeCodes as $attributeCode) {
                $pqb = $this->pqbFactory->create();
                $publishedProducts = $pqb
                    ->addFilter($attributeCode, Operators::IN_LIST, [$asset->getCode()])
                    ->execute();

                if ($publishedProducts->count() > 0) {
                    throw new PublishedProductConsistencyException(
                        'Impossible to remove an asset linked to a published product'
                    );
                }
            }
        }

        return $event;
    }
}
