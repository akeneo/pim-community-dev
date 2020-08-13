<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Attribute;

use Akeneo\Pim\Automation\DataQualityInsights\Application\StructureEvaluation\EvaluateUpdatedAttributeOptions;
use Akeneo\Pim\Automation\DataQualityInsights\Application\StructureEvaluation\EvaluateUpdatedAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\AttributeOptionWordIgnoredEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\AttributeWordIgnoredEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class InitializeEvaluationSubscriber implements EventSubscriberInterface
{
    /** @var EvaluateUpdatedAttributes */
    private $evaluateUpdatedAttributes;

    /** @var EvaluateUpdatedAttributeOptions */
    private $evaluateUpdatedAttributeOptions;

    /** @var FeatureFlag */
    private $dataQualityInsightsFeature;

    public function __construct(
        EvaluateUpdatedAttributes $evaluateUpdatedAttributes,
        EvaluateUpdatedAttributeOptions $evaluateUpdatedAttributeOptions,
        FeatureFlag $dataQualityInsightsFeature
    ) {
        $this->evaluateUpdatedAttributes = $evaluateUpdatedAttributes;
        $this->evaluateUpdatedAttributeOptions = $evaluateUpdatedAttributeOptions;
        $this->dataQualityInsightsFeature = $dataQualityInsightsFeature;
    }

    public static function getSubscribedEvents()
    {
        return [
            AttributeWordIgnoredEvent::class => 'onIgnoredWord',
            AttributeOptionWordIgnoredEvent::class => 'onIgnoredOptionWord',
            StorageEvents::POST_SAVE => 'onPostSave',
        ];
    }

    public function onIgnoredWord(AttributeWordIgnoredEvent $event): void
    {
        $this->evaluateUpdatedAttributes->evaluate($event->getAttributeCode());
    }

    public function onIgnoredOptionWord(AttributeOptionWordIgnoredEvent $event): void
    {
        $this->evaluateUpdatedAttributeOptions->evaluate($event->getAttributeOptionCode());
    }

    public function onPostSave(GenericEvent $event)
    {
        $subject = $event->getSubject();
        if (!$subject instanceof AttributeInterface && !$subject instanceof AttributeOptionInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        if (! $this->dataQualityInsightsFeature->isEnabled()) {
            return;
        }

        if ($subject instanceof AttributeInterface) {
            $this->handleAttributePostSave($subject);
            return;
        }

        if ($subject instanceof AttributeOptionInterface) {
            $this->handleAttributeOptionPostSave($subject);
            return;
        }
    }

    private function handleAttributePostSave(AttributeInterface $attribute): void
    {
        $attributeCode = new AttributeCode($attribute->getCode());
        $this->evaluateUpdatedAttributes->evaluate($attributeCode);
    }

    private function handleAttributeOptionPostSave(AttributeOptionInterface $attributeOption): void
    {
        $attributeCode = new AttributeCode($attributeOption->getAttribute()->getCode());
        $attributeOptionCode = new AttributeOptionCode($attributeCode, $attributeOption->getCode());

        $this->evaluateUpdatedAttributeOptions->evaluate($attributeOptionCode);
    }
}
