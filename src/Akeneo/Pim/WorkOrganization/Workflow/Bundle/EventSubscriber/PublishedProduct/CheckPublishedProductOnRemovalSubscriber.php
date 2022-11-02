<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct;

use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Exception\PublishedProductConsistencyException;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Check some pre remove events and forbid deletion if the entity is linked to a published product
 * Warning: Prefer to use a Remove command for your object, then add a validation checking the object can be removed.
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class CheckPublishedProductOnRemovalSubscriber implements EventSubscriberInterface
{
    protected PublishedProductRepositoryInterface $publishedRepository;
    protected ProductQueryBuilderFactoryInterface $queryBuilderFactory;
    protected ChannelRepositoryInterface $channelRepository;
    protected LocaleRepositoryInterface $localeRepository;
    private TranslatorInterface $translator;

    public function __construct(
        PublishedProductRepositoryInterface $publishedRepository,
        ProductQueryBuilderFactoryInterface $queryBuilderFactory,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        TranslatorInterface $translator
    ) {
        $this->publishedRepository = $publishedRepository;
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_REMOVE => 'preRemove',
        ];
    }

    /**
     * Check if the family is linked to a published product
     *
     * @throws PublishedProductConsistencyException
     */
    public function preRemove(GenericEvent $event)
    {
        $subject = $event->getSubject();

        if (!$this->isSubjectRelatedToPublished($subject)) {
            return;
        }

        $message = 'pimee_workflow.check_removal.entity_error';

        if ($subject instanceof ProductInterface) {
            $message = 'pimee_workflow.check_removal.product_error';
        }

        /*
         * It should not be the responsibility of this subscriber to format a translated message.
         * Prefer to validate the Remove command instead (see class comment)
         */
        throw new PublishedProductConsistencyException($this->translator->trans($message));
    }

    /**
     * @param mixed $subject
     */
    private function isSubjectRelatedToPublished($subject): bool
    {
        if (!is_object($subject)) {
            return false;
        }

        if ($subject instanceof PublishedProductInterface) {
            return false;
        }

        if ($subject instanceof FamilyInterface) {
            return $this->countPublishedProductsForFamily($subject) > 0;
        }

        if ($subject instanceof GroupInterface) {
            return $this->countPublishedProductsForGroup($subject) > 0;
        }

        if ($subject instanceof AssociationTypeInterface) {
            return $this->publishedRepository->countPublishedProductsForAssociationType($subject) > 0;
        }

        if ($subject instanceof CategoryInterface) {
            return $this->countPublishedProductsForCategory($subject) > 0;
        }

        if ($subject instanceof AttributeInterface) {
            if (!$subject->isLocalizable()) {
                return $this->countPublishedProductsForNonLocalizableAttribute($subject) > 0;
            } else {
                return $this->countPublishedProductsForLocalizableAttribute($subject) > 0;
            }
        }

        if ($subject instanceof AttributeOptionInterface) {
            return $this->countPublishedProductsForAttributeOption($subject) > 0;
        }

        if ($subject instanceof ProductInterface) {
            return null !== $this->publishedRepository->findOneByOriginalProduct($subject);
        }

        return false;
    }

    private function countPublishedProductsForFamily(FamilyInterface $family): int
    {
        $productQb = $this->queryBuilderFactory->create();
        $productQb->addFilter('family', Operators::IN_LIST, [$family->getCode()]);

        return $productQb->execute()->count();
    }

    private function countPublishedProductsForCategory(CategoryInterface $category): int
    {
        $productQb = $this->queryBuilderFactory->create();
        $productQb->addFilter('categories', Operators::IN_CHILDREN_LIST, [$category->getCode()]);

        return $productQb->execute()->count();
    }

    private function countPublishedProductsForGroup(GroupInterface $group): int
    {
        $productQb = $this->queryBuilderFactory->create();
        $productQb->addFilter('groups', Operators::IN_LIST, [$group->getCode()]);

        return $productQb->execute()->count();
    }

    private function countPublishedProductsForAttributeOption(AttributeOptionInterface $option): int
    {
        $count = 0;
        $channelCodes = $option->getAttribute()->isScopable()
            ? $this->channelRepository->getChannelCodes()
            : [null] ;
        $localeCodes = $option->getAttribute()->isLocalizable()
            ? $this->localeRepository->getActivatedLocaleCodes()
            : [null] ;

        foreach ($channelCodes as $channelCode) {
            foreach ($localeCodes as $localeCode) {
                $productQb = $this->queryBuilderFactory->create();
                $productQb->addFilter(
                    $option->getAttribute()->getCode(),
                    Operators::IN_LIST,
                    [$option->getCode()],
                    [
                        'scope' => $channelCode,
                        'locale' => $localeCode,
                    ]
                );

                $count += $productQb->execute()->count();
            }
        }

        return $count;
    }

    private function countPublishedProductsForNonLocalizableAttribute(AttributeInterface $attribute): int
    {
        if ($attribute->isScopable()) {
            $count = 0;
            $channelCodes = $this->channelRepository->getChannelCodes();

            foreach ($channelCodes as $channelCode) {
                $productQb = $this->queryBuilderFactory->create();
                $productQb->addFilter($attribute->getCode(), Operators::IS_NOT_EMPTY, '', ['scope'  => $channelCode]);

                $count += $productQb->execute()->count();
            }
        } else {
            $productQb = $this->queryBuilderFactory->create();
            $productQb->addFilter($attribute->getCode(), Operators::IS_NOT_EMPTY, '');

            $count = $productQb->execute()->count();
        }

        return $count;
    }

    private function countPublishedProductsForLocalizableAttribute(AttributeInterface $attribute): int
    {
        $count = 0;

        if ($attribute->isLocaleSpecific()) {
            $localeCodes = $attribute->getAvailableLocaleCodes();
        } else {
            $localeCodes = $this->localeRepository->getActivatedLocaleCodes();
        }

        if (!$attribute->isScopable()) {
            foreach ($localeCodes as $localeCode) {
                $productQb = $this->queryBuilderFactory->create();
                $productQb->addFilter($attribute->getCode(), Operators::IS_NOT_EMPTY, '', ['locale'  => $localeCode]);

                $count += $productQb->execute()->count();
            }
        } else {
            $channels = $this->channelRepository->findAll();

            foreach ($channels as $channel) {
                foreach ($channel->getLocaleCodes() as $localeCode) {
                    if (in_array($localeCode, $localeCodes)) {
                        $productQb = $this->queryBuilderFactory->create();
                        $productQb->addFilter(
                            $attribute->getCode(),
                            Operators::IS_NOT_EMPTY,
                            '',
                            [
                                'locale' => $localeCode,
                                'scope'  => $channel->getCode(),
                            ]
                        );

                        $count += $productQb->execute()->count();
                    }
                }
            }
        }

        return $count;
    }
}
