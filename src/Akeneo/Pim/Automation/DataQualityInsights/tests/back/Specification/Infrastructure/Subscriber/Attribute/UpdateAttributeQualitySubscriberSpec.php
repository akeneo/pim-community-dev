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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\Attribute;

use Akeneo\Pim\Automation\DataQualityInsights\Application\StructureEvaluation\ComputeAttributeQuality;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\Structure\AttributeLabelsSpellingEvaluatedEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\Structure\AttributeOptionLabelsSpellingEvaluatedEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\AttributeQualityRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\Quality;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

final class UpdateAttributeQualitySubscriberSpec extends ObjectBehavior
{
    public function let(
        ComputeAttributeQuality $computeAttributeQuality,
        AttributeQualityRepositoryInterface $attributeQualityRepository,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($computeAttributeQuality, $attributeQualityRepository, $logger);
    }

    public function it_does_not_crash_if_an_exception_is_thrown_during_attribute_quality_computing(
          $computeAttributeQuality,
          $logger
    ) {
        $attributeCode = new AttributeCode('color');
        $attributeSpellcheck = new AttributeSpellcheck(
            $attributeCode,
            new \DateTimeImmutable(),
            new SpellcheckResultByLocaleCollection()
        );

        $computeAttributeQuality->byAttributeCode($attributeCode)->willThrow(new \Exception('Fail'));
        $logger->error(Argument::cetera())->shouldBeCalled();

        $this->onAttributeLabelsSpellingEvaluated(new AttributeLabelsSpellingEvaluatedEvent($attributeSpellcheck));
    }

    public function it_does_not_crash_if_an_exception_is_thrown_during_attribute_quality_saving(
          $computeAttributeQuality,
          $attributeQualityRepository,
          $logger
    ) {
        $attributeCode = new AttributeCode('color');
        $attributeOptionSpellcheck = new AttributeOptionSpellcheck(
            new AttributeOptionCode($attributeCode, 'red'),
            new \DateTimeImmutable(),
            new SpellcheckResultByLocaleCollection()
        );
        $quality = Quality::toImprove();

        $computeAttributeQuality->byAttributeCode($attributeCode)->willReturn($quality);
        $attributeQualityRepository->save($attributeCode, $quality)->willThrow(new \Exception('Fail'));
        $logger->error(Argument::cetera())->shouldBeCalled();

        $this->onAttributeOptionLabelsSpellingEvaluated(
            new AttributeOptionLabelsSpellingEvaluatedEvent($attributeOptionSpellcheck)
        );
    }
}
