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

use Akeneo\Pim\Automation\DataQualityInsights\Application\StructureEvaluation\ConsolidateAttributeQuality;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\Structure\AttributeLabelsSpellingEvaluatedEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\Structure\AttributeOptionLabelsSpellingEvaluatedEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\AttributeQualityRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class UpdateAttributeQualitySubscriber implements EventSubscriberInterface
{
    private ConsolidateAttributeQuality $consolidateAttributeQuality;
    private LoggerInterface $logger;

    public function __construct(
        ConsolidateAttributeQuality $consolidateAttributeQuality,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->consolidateAttributeQuality = $consolidateAttributeQuality;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AttributeLabelsSpellingEvaluatedEvent::class => 'onAttributeLabelsSpellingEvaluated',
            AttributeOptionLabelsSpellingEvaluatedEvent::class => 'onAttributeOptionLabelsSpellingEvaluated',
        ];
    }

    public function onAttributeLabelsSpellingEvaluated(AttributeLabelsSpellingEvaluatedEvent $event): void
    {
        $this->updateAttributeQuality($event->getAttributeSpellcheck()->getAttributeCode());
    }

    public function onAttributeOptionLabelsSpellingEvaluated(AttributeOptionLabelsSpellingEvaluatedEvent $event): void
    {
        $this->updateAttributeQuality($event->getAttributeOptionSpellcheck()->getAttributeCode());
    }

    private function updateAttributeQuality(AttributeCode $attributeCode): void
    {
        try {
            $this->consolidateAttributeQuality->byAttributeCode($attributeCode);
        } catch (\Throwable $exception) {
            $this->logger->error(
                sprintf('Unable to consolidate quality of attribute "%s"', $attributeCode),
                ['error_code' => 'unable_to_consolidate_attribute_quality', 'error_message' => $exception->getMessage()]
            );
        }
    }
}
