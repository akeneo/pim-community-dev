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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\StructureEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\StructureEvaluation\EvaluateAttributeOptionLabelsSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\Structure\AttributeOptionLabelsSpellingEvaluatedEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeOptionCodesToEvaluateQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\AttributeOptionSpellcheckRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EvaluateUpdatedAttributeOptionsSpec extends ObjectBehavior
{
    public function let(
        AttributeOptionSpellcheckRepositoryInterface $attributeOptionSpellcheckRepository,
        EvaluateAttributeOptionLabelsSpelling $evaluateAttributeOptionLabelsSpelling,
        GetAttributeOptionCodesToEvaluateQueryInterface $getAttributeOptionCodesToEvaluateQuery,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith($attributeOptionSpellcheckRepository, $evaluateAttributeOptionLabelsSpelling, $getAttributeOptionCodesToEvaluateQuery, $eventDispatcher);
    }

    public function it_evaluates_all_the_attribute_options_updated_since_their_last_evaluation(
        $attributeOptionSpellcheckRepository,
        $evaluateAttributeOptionLabelsSpelling,
        $getAttributeOptionCodesToEvaluateQuery,
        $eventDispatcher
    ) {
        $sinceDate = new \DateTimeImmutable('now -1day');
        $colorRed = new AttributeOptionCode(new AttributeCode('color'), 'red');
        $materialWood = new AttributeOptionCode(new AttributeCode('material'), 'wood');

        $getAttributeOptionCodesToEvaluateQuery->execute($sinceDate)->willReturn(new \ArrayIterator([
            $colorRed, $materialWood
        ]));

        $colorRedSpellcheck = new AttributeOptionSpellcheck(
            $colorRed,
            new \DateTimeImmutable('now -2days'),
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::good())
                ->add(new LocaleCode('fr_FR'), SpellCheckResult::toImprove())
        );

        $materialWoodSpellcheck = new AttributeOptionSpellcheck(
            $materialWood,
            new \DateTimeImmutable('now -2days'),
            new SpellcheckResultByLocaleCollection()
        );

        $evaluateAttributeOptionLabelsSpelling->evaluate($colorRed)->willReturn($colorRedSpellcheck);
        $evaluateAttributeOptionLabelsSpelling->evaluate($materialWood)->willReturn($materialWoodSpellcheck);

        $attributeOptionSpellcheckRepository->save($colorRedSpellcheck)->shouldBeCalled();
        $attributeOptionSpellcheckRepository->save($materialWoodSpellcheck)->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::type(AttributeOptionLabelsSpellingEvaluatedEvent::class))->shouldBeCalledTimes(2);

        $attributeOptionSpellcheckRepository->deleteUnknownAttributeOptions()->shouldBeCalled();

        $this->evaluateSince($sinceDate);
    }
}
