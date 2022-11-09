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

use Akeneo\Pim\Automation\DataQualityInsights\Application\StructureEvaluation\EvaluateAttributeLabelsSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\Structure\AttributeLabelsSpellingEvaluatedEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributesCodesToEvaluateQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\AttributeSpellcheckRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EvaluateUpdatedAttributesSpec extends ObjectBehavior
{
    public function let(
        EvaluateAttributeLabelsSpelling $evaluateAttributeLabelsSpelling,
        AttributeSpellcheckRepositoryInterface $attributeSpellcheckRepository,
        GetAttributesCodesToEvaluateQueryInterface $getAttributesToEvaluateQuery,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith($evaluateAttributeLabelsSpelling, $attributeSpellcheckRepository, $getAttributesToEvaluateQuery, $eventDispatcher);
    }

    public function it_evaluates_all_the_attributes_updated_since_their_last_evaluation(
        $evaluateAttributeLabelsSpelling,
        $attributeSpellcheckRepository,
        $getAttributesToEvaluateQuery,
        $eventDispatcher
    ) {
        $name = new AttributeCode('name');
        $description = new AttributeCode('description');
        $size = new AttributeCode('size');

        $getAttributesToEvaluateQuery->execute()->willReturn(new \ArrayIterator([
            $name,
            $description,
        ]));

        $getAttributesToEvaluateQuery->toReevaluate()->willReturn(new \ArrayIterator([
            $size
        ]));

        $nameSpellcheck = new AttributeSpellcheck(
            $name,
            new \DateTimeImmutable(),
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::good())
                ->add(new LocaleCode('fr_FR'), SpellCheckResult::toImprove())
        );
        $descriptionSpellcheck = new AttributeSpellcheck(
            $description,
            new \DateTimeImmutable(),
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::good())
        );
        $sizeSpellcheck = new AttributeSpellcheck(
            $name,
            new \DateTimeImmutable(),
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::good())
                ->add(new LocaleCode('fr_FR'), SpellCheckResult::toImprove())
        );

        $evaluateAttributeLabelsSpelling->evaluate($name)->willReturn($nameSpellcheck);
        $evaluateAttributeLabelsSpelling->evaluate($description)->willReturn($descriptionSpellcheck);
        $evaluateAttributeLabelsSpelling->evaluate($size)->willReturn($sizeSpellcheck);

        $attributeSpellcheckRepository->save($nameSpellcheck)->shouldBeCalled();
        $attributeSpellcheckRepository->save($descriptionSpellcheck)->shouldBeCalled();
        $attributeSpellcheckRepository->save($sizeSpellcheck)->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::type(AttributeLabelsSpellingEvaluatedEvent::class))->shouldBeCalledTimes(3);

        $attributeSpellcheckRepository->deleteUnknownAttributes()->shouldBeCalled();

        $this->evaluateAll();
    }

    public function it_evaluates_one_attribute(
        $evaluateAttributeLabelsSpelling,
        $attributeSpellcheckRepository,
        $eventDispatcher
    ) {
        $name = new AttributeCode('name');

        $nameSpellcheck = new AttributeSpellcheck(
            $name,
            new \DateTimeImmutable(),
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::good())
                ->add(new LocaleCode('fr_FR'), SpellCheckResult::toImprove())
        );

        $evaluateAttributeLabelsSpelling->evaluate($name)->willReturn($nameSpellcheck);

        $attributeSpellcheckRepository->save($nameSpellcheck)->shouldBeCalled();

        $eventDispatcher->dispatch(Argument::type(AttributeLabelsSpellingEvaluatedEvent::class))->shouldBeCalled();

        $this->evaluate($name);
    }
}
