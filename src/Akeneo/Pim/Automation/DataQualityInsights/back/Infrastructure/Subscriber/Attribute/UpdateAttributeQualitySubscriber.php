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

use Akeneo\Pim\Automation\DataQualityInsights\Application\StructureEvaluation\ComputeAttributeQuality;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\Structure\AttributeLabelsSpellingEvaluatedEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\Structure\AttributeOptionLabelsSpellingEvaluatedEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\AttributeQualityRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class UpdateAttributeQualitySubscriber implements EventSubscriberInterface
{
    /** @var ComputeAttributeQuality */
    private $computeAttributeQuality;

    /** @var AttributeQualityRepositoryInterface */
    private $attributeQualityRepository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ComputeAttributeQuality $computeAttributeQuality,
        AttributeQualityRepositoryInterface $attributeQualityRepository,
        LoggerInterface $logger
    ) {
        $this->computeAttributeQuality = $computeAttributeQuality;
        $this->attributeQualityRepository = $attributeQualityRepository;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
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
            $quality = $this->computeAttributeQuality->byAttributeCode($attributeCode);
            $this->attributeQualityRepository->save($attributeCode, $quality);
        } catch (\Throwable $exception) {
            $this->logger->error(sprintf('Unable to update quality of attribute "%s"', $attributeCode),
                ['error_code' => 'unable_to_update_attribute_quality', 'error_message' => $exception->getMessage()]
            );
        }
    }
}
