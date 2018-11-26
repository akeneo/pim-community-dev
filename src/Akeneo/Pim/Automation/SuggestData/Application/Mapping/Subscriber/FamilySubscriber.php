<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Application\Mapping\Subscriber;

use Akeneo\Pim\Automation\SuggestData\Application\Connector\JobInstanceNames;
use Akeneo\Pim\Automation\SuggestData\Application\Connector\JobLauncherInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\FamilyAttribute\Query\FindFamilyAttributesNotInQueryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class FamilySubscriber implements EventSubscriberInterface
{
    /** @var FindFamilyAttributesNotInQueryInterface */
    private $query;

    /** @var JobLauncherInterface */
    private $jobLauncher;

    /**
     * @param FindFamilyAttributesNotInQueryInterface $query
     * @param JobLauncherInterface $jobLauncher
     */
    public function __construct(
        FindFamilyAttributesNotInQueryInterface $query,
        JobLauncherInterface $jobLauncher
    ) {
        $this->query = $query;
        $this->jobLauncher = $jobLauncher;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [StorageEvents::PRE_SAVE => 'updateAttributesMapping'];
    }

    /**
     * @param GenericEvent $event
     */
    public function updateAttributesMapping(GenericEvent $event): void
    {
        $family = $event->getSubject();
        if (!$family instanceof FamilyInterface || null === $family->getId()) {
            return;
        }

        $removedAttributes = $this->query->findFamilyAttributesNotIn($family->getCode(), $family->getAttributeCodes());

        if (!empty($removedAttributes)) {
            $this->jobLauncher->launch(
                JobInstanceNames::REMOVE_ATTRIBUTES_FROM_MAPPING,
                [
                    'pim_attribute_codes' => $removedAttributes,
                    'family_code' => $family->getCode(),
                ]
            );
        }
    }
}
