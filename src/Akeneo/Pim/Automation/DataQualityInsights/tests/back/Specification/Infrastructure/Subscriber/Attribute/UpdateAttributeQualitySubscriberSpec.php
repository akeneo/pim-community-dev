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

use Akeneo\Pim\Automation\DataQualityInsights\Application\StructureEvaluation\ConsolidateAttributeQuality;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\Structure\AttributeLabelsSpellingEvaluatedEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\AttributeQualityRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

final class UpdateAttributeQualitySubscriberSpec extends ObjectBehavior
{
    public function let(
        ConsolidateAttributeQuality $consolidateAttributeQuality,
        AttributeQualityRepositoryInterface $attributeQualityRepository,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($consolidateAttributeQuality, $attributeQualityRepository, $logger);
    }

    public function it_does_not_crash_if_an_exception_is_thrown_during_attribute_quality_consolidation(
          $consolidateAttributeQuality,
          $logger
    ) {
        $attributeCode = new AttributeCode('color');
        $attributeSpellcheck = new AttributeSpellcheck(
            $attributeCode,
            new \DateTimeImmutable(),
            new SpellcheckResultByLocaleCollection()
        );

        $consolidateAttributeQuality->byAttributeCode($attributeCode)->willThrow(new \Exception('Fail'));
        $logger->error(Argument::cetera())->shouldBeCalled();

        $this->onAttributeLabelsSpellingEvaluated(new AttributeLabelsSpellingEvaluatedEvent($attributeSpellcheck));
    }
}
