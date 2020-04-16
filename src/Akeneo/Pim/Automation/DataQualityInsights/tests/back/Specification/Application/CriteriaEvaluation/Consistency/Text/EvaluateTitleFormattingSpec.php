<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Text;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Text\EvaluateTitleFormatting;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Text\TitleFormattingServiceInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\UnableToProvideATitleSuggestion;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleDataCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValues;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetIgnoredProductTitleSuggestionQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductTitle;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PhpSpec\ObjectBehavior;

class EvaluateTitleFormattingSpec extends ObjectBehavior
{
    public function let(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        TitleFormattingServiceInterface $titleFormattingService,
        GetIgnoredProductTitleSuggestionQueryInterface $getIgnoredProductTitleSuggestionQuery
    ) {
        $this->beConstructedWith(
            $localesByChannelQuery,
            $titleFormattingService,
            $getIgnoredProductTitleSuggestionQuery
        );
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(EvaluateTitleFormatting::class);
        $this->shouldImplement(EvaluateCriterionInterface::class);
    }

    public function it_set_evaluation_status_as_not_applicable_when_a_product_has_no_attribute_has_main_title(
        GetLocalesByChannelQueryInterface $localesByChannelQuery
    ) {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection(
            [
                'ecommerce' => ['en_US', 'fr_FR'],
            ]
        ));

        $expectedResult = (new Write\CriterionEvaluationResult())
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::notApplicable())
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), CriterionEvaluationResultStatus::notApplicable())
        ;

        $this->evaluate(
            new Write\CriterionEvaluation(
                new CriterionEvaluationId(),
                new CriterionCode('criterion1'),
                new ProductId(1),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::pending()
            ),
            new ProductValuesCollection()
        )->shouldBeLike($expectedResult);
    }

    public function it_evaluates_title_without_suggestions(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        TitleFormattingServiceInterface $titleFormattingService
    ) {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection(
            [
                'ecommerce' => ['en_US', 'fr_FR'],
                'mobile' => ['en_US', 'fr_FR'],
                'print' => ['en_US', 'fr_FR'],
            ]
        ));

        $mainTitleAttribute = $this->givenAMainTitleAttribute('attribute_as_main_title_localizable_scopable');
        $attributeTextNotToEvaluate = $this->givenAnAttributeOfTypeText('whatever');

        $mainTitleValues = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => 'MacBook Pro Retina 13"',
                'fr_FR' => 'Titre non evalué',
            ],
            'mobile' => [
                'en_US' => 'MacBook Pro Retina 13"',
                'fr_FR' => null,
            ],
            'print' => [
                'en_US' => 'MacBook Pro Retina 13"',
                'fr_FR' => null,
            ],
        ], function ($value) { return $value; });

        $textValuesNotToEvaluate = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => 'Whatever',
            ],
        ], function ($value) { return $value; });

        $productValues = (new ProductValuesCollection())
            ->add(new ProductValues($mainTitleAttribute, $mainTitleValues))
            ->add(new ProductValues($attributeTextNotToEvaluate, $textValuesNotToEvaluate));

        $titleFormattingService->format(new ProductTitle('MacBook Pro Retina 13"'))->shouldBeCalledTimes(3)->willReturn(new ProductTitle('MacBook Pro Retina 13"'));
        $titleFormattingService->format(new ProductTitle('Titre non evalué'))->shouldNotBeCalled();

        $expectedResult = (new Write\CriterionEvaluationResult())
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(100))
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::done())

            ->addRate(new ChannelCode('mobile'), new LocaleCode('en_US'), new Rate(100))
            ->addStatus(new ChannelCode('mobile'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::done())

            ->addRate(new ChannelCode('print'), new LocaleCode('en_US'), new Rate(100))
            ->addStatus(new ChannelCode('print'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::done())

            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), CriterionEvaluationResultStatus::notApplicable())
            ->addStatus(new ChannelCode('mobile'), new LocaleCode('fr_FR'), CriterionEvaluationResultStatus::notApplicable())
            ->addStatus(new ChannelCode('print'), new LocaleCode('fr_FR'), CriterionEvaluationResultStatus::notApplicable())
        ;

        $this->evaluate(
            new Write\CriterionEvaluation(
                new CriterionEvaluationId(),
                new CriterionCode('criterion1'),
                new ProductId(1),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::pending()
            ),
            $productValues
        )->shouldBeLike($expectedResult);
    }

    public function it_evaluates_title_with_suggestions(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        TitleFormattingServiceInterface $titleFormattingService
    ) {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection(
            [
                'ecommerce' => ['en_US', 'fr_FR'],
            ]
        ));

        $mainTitleAttribute = $this->givenAMainTitleAttribute('attribute_as_main_title_localizable_scopable');
        $attributeTextNotToEvaluate = $this->givenAnAttributeOfTypeText('whatever');

        $mainTitleValues = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => 'Macbook Pro Retina 13" Azerty',
                'fr_FR' => 'Titre non evalué',
            ],
        ], function ($value) { return $value; });

        $textValuesNotToEvaluate = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => 'Whatever',
            ],
        ], function ($value) { return $value; });

        $productValues = (new ProductValuesCollection())
            ->add(new ProductValues($mainTitleAttribute, $mainTitleValues))
            ->add(new ProductValues($attributeTextNotToEvaluate, $textValuesNotToEvaluate));

        $titleFormattingService->format(new ProductTitle('Macbook Pro Retina 13" Azerty'))->shouldBeCalled()->willReturn(new ProductTitle('MacBook Pro Retina 13" AZERTY'));
        $titleFormattingService->format(new ProductTitle('Titre non evalué'))->shouldNotBeCalled();

        $expectedResult = (new Write\CriterionEvaluationResult())
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(76))
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::done())
            ->addImprovableAttributes(new ChannelCode('ecommerce'), new LocaleCode('en_US'), ['attribute_as_main_title_localizable_scopable'])
            ->addData('suggestions', new ChannelCode('ecommerce'), new LocaleCode('en_US'), 'MacBook Pro Retina 13" AZERTY')
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), CriterionEvaluationResultStatus::notApplicable())
        ;

        $this->evaluate(
            new Write\CriterionEvaluation(
                new CriterionEvaluationId(),
                new CriterionCode('criterion1'),
                new ProductId(1),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::pending()
            ),
            $productValues
        )->shouldBeLike($expectedResult);
    }

    public function it_evaluates_title_with_suggestions_with_two_en_locales(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        TitleFormattingServiceInterface $titleFormattingService
    ) {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection(
            [
                'ecommerce' => ['en_US', 'en_GB'],
            ]
        ));

        $mainTitleAttribute = $this->givenAMainTitleAttribute('attribute_as_main_title_localizable_scopable');
        $mainTitleValues = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => 'Macbook Pro Retina 13" Azerty',
                'en_GB' => 'MacBook Pro Retina 13" Azerty',
            ],
        ], function ($value) { return $value; });

        $productValues = (new ProductValuesCollection())->add(new ProductValues($mainTitleAttribute, $mainTitleValues));

        $titleFormattingService->format(new ProductTitle('Macbook Pro Retina 13" Azerty'))->shouldBeCalled()->willReturn(new ProductTitle('MacBook Pro Retina 13" AZERTY'));
        $titleFormattingService->format(new ProductTitle('MacBook Pro Retina 13" Azerty'))->shouldBeCalled()->willReturn(new ProductTitle('MacBook Pro Retina 13" AZERTY'));

        $expectedResult = (new Write\CriterionEvaluationResult())
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(76))
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::done())
            ->addImprovableAttributes(new ChannelCode('ecommerce'), new LocaleCode('en_US'), ['attribute_as_main_title_localizable_scopable'])
            ->addData('suggestions', new ChannelCode('ecommerce'), new LocaleCode('en_US'), 'MacBook Pro Retina 13" AZERTY')

            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_GB'), new Rate(88))
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('en_GB'), CriterionEvaluationResultStatus::done())
            ->addImprovableAttributes(new ChannelCode('ecommerce'), new LocaleCode('en_GB'), ['attribute_as_main_title_localizable_scopable'])
            ->addData('suggestions', new ChannelCode('ecommerce'), new LocaleCode('en_GB'), 'MacBook Pro Retina 13" AZERTY')
        ;

        $this->evaluate(
            new Write\CriterionEvaluation(
                new CriterionEvaluationId(),
                new CriterionCode('criterion1'),
                new ProductId(1),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::pending()
            ),
            $productValues
        )->shouldBeLike($expectedResult);
    }
    public function it_evaluates_title_with_ignored_suggestions(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        TitleFormattingServiceInterface $titleFormattingService,
        GetIgnoredProductTitleSuggestionQueryInterface $getIgnoredProductTitleSuggestionQuery
    ) {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection(
            [
                'ecommerce' => ['en_US'],
            ]
        ));

        $productId = new ProductId(1);

        $mainTitleAttribute = $this->givenAMainTitleAttribute('attribute_as_main_title_localizable_scopable');
        $mainTitleValues = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => 'Macbook Pro Retina 13" Azerty',
            ],
        ], function ($value) { return $value; });

        $productValues = (new ProductValuesCollection())->add(new ProductValues($mainTitleAttribute, $mainTitleValues));

        $titleFormattingService->format(new ProductTitle('Macbook Pro Retina 13" Azerty'))->shouldBeCalled()->willReturn(new ProductTitle('MacBook Pro Retina 13" AZERTY'));

        $getIgnoredProductTitleSuggestionQuery->execute($productId, new ChannelCode('ecommerce'), new LocaleCode('en_US'))->shouldBeCalled()->willReturn('MacBook Pro Retina 13" AZERTY');

        $expectedResult = (new Write\CriterionEvaluationResult())
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(100))
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::done());

        $this->evaluate(
            new Write\CriterionEvaluation(
                new CriterionEvaluationId(),
                new CriterionCode('criterion1'),
                $productId,
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::pending()
            ),
            $productValues
        )->shouldBeLike($expectedResult);
    }

    public function it_sets_status_in_error_when_a_title_formatting_fails(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        TitleFormattingServiceInterface $titleFormattingService
    ) {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection([
            'mobile' => ['en_US', 'en_CA'],
        ]));

        $mainTitleAttribute = $this->givenAMainTitleAttribute('attribute_as_main_title_localizable_scopable');
        $mainTitleValues = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'mobile' => [
                'en_US' => 'MacBook Pro Retina 13"',
                'en_CA' => 'Fail',
            ],
        ], function ($value) { return $value; });

        $productValues = (new ProductValuesCollection())->add(new ProductValues($mainTitleAttribute, $mainTitleValues));

        $titleFormattingService->format(new ProductTitle('MacBook Pro Retina 13"'))->willReturn(new ProductTitle('MacBook Pro Retina 13"'));
        $titleFormattingService->format(new ProductTitle('Fail'))->willThrow(new UnableToProvideATitleSuggestion());

        $expectedResult = (new Write\CriterionEvaluationResult())
            ->addRate(new ChannelCode('mobile'), new LocaleCode('en_US'), new Rate(100))
            ->addStatus(new ChannelCode('mobile'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::done())
            ->addStatus(new ChannelCode('mobile'), new LocaleCode('en_CA'), CriterionEvaluationResultStatus::error())
        ;

        $this->evaluate(
            new Write\CriterionEvaluation(
                new CriterionEvaluationId(),
                new CriterionCode('criterion1'),
                new ProductId(1),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::pending()
            ),
            $productValues
        )->shouldBeLike($expectedResult);
    }

    public function it_evaluates_its_applicability_for_a_collection_of_product_values_that_have_at_least_one_applicable_value(
        GetLocalesByChannelQueryInterface $localesByChannelQuery
    ) {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection([
            'ecommerce' => ['en_US', 'fr_FR'],
            'mobile' => ['en_US'],
        ]));

        $mainTitleAttribute = $this->givenAMainTitleAttribute('attribute_as_main_title');
        $mainTitleValues = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => 'MacBook Pro Retina 13"',
                'fr_FR' => 'MacBook Pro Retina 13 pouces',
            ],
            'mobile' => [
                'en_US' => null
            ]
        ], function ($value) { return $value; });

        $productValues = (new ProductValuesCollection())->add(new ProductValues($mainTitleAttribute, $mainTitleValues));

        $expectedResult = (new Write\CriterionEvaluationResult())
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), CriterionEvaluationResultStatus::notApplicable())
            ->addStatus(new ChannelCode('mobile'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::notApplicable())
        ;

        $this->evaluateApplicability($productValues)->shouldBeLike(new Write\CriterionApplicability($expectedResult, true));
    }

    public function it_evaluates_its_applicability_for_a_collection_of_product_values_that_has_no_applicable_value(
        GetLocalesByChannelQueryInterface $localesByChannelQuery
    ) {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection([
            'ecommerce' => ['en_US', 'fr_FR'],
            'mobile' => ['en_US'],
        ]));

        $mainTitleAttribute = $this->givenAMainTitleAttribute('attribute_as_main_title');
        $mainTitleValues = ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => '',
                'fr_FR' => 'MacBook Pro Retina 13 pouces',
            ],
        ], function ($value) { return $value; });

        $productValues = (new ProductValuesCollection())->add(new ProductValues($mainTitleAttribute, $mainTitleValues));

        $expectedResult = (new Write\CriterionEvaluationResult())
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::notApplicable())
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), CriterionEvaluationResultStatus::notApplicable())
            ->addStatus(new ChannelCode('mobile'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::notApplicable())
        ;

        $this->evaluateApplicability($productValues)->shouldBeLike(new Write\CriterionApplicability($expectedResult, false));
    }

    private function givenAMainTitleAttribute(string $code): Attribute
    {
        return new Attribute(new AttributeCode($code), AttributeType::text(), true, true);
    }

    private function givenAnAttributeOfTypeText(string $code): Attribute
    {
        return new Attribute(new AttributeCode($code), AttributeType::text(), true, false);
    }
}
