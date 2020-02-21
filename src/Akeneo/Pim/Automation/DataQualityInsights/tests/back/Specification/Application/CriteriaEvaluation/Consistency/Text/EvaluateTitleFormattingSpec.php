<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Text;

use Akeneo\Pim\Automation\DataQualityInsights\Application\BuildProductValuesInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Text\EvaluateTitleFormatting;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Text\TitleFormattingServiceInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\UnableToProvideATitleSuggestion;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetAttributeAsMainTitleFromProductIdInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetIgnoredProductTitleSuggestionQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
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
        BuildProductValuesInterface $buildProductValues,
        GetAttributeAsMainTitleFromProductIdInterface $getAttributeAsMainTitle,
        TitleFormattingServiceInterface $titleFormattingService,
        GetIgnoredProductTitleSuggestionQueryInterface $getIgnoredProductTitleSuggestionQuery
    ) {
        $this->beConstructedWith(
            $localesByChannelQuery,
            $buildProductValues,
            $getAttributeAsMainTitle,
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
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        GetAttributeAsMainTitleFromProductIdInterface $getAttributeAsMainTitle
    ) {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection(
            [
                'ecommerce' => ['en_US', 'fr_FR'],
            ]
        ));

        $productId = new ProductId(1);
        $getAttributeAsMainTitle->execute($productId)->willReturn(null);

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
            )
        )->shouldBeLike($expectedResult);
    }

    public function it_evaluates_title_without_suggestions(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        BuildProductValuesInterface $buildProductValues,
        GetAttributeAsMainTitleFromProductIdInterface $getAttributeAsMainTitle,
        TitleFormattingServiceInterface $titleFormattingService
    ) {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection(
            [
                'ecommerce' => ['en_US', 'fr_FR'],
                'mobile' => ['en_US', 'fr_FR'],
                'print' => ['en_US', 'fr_FR'],
            ]
        ));

        $productId = new ProductId(1);
        $getAttributeAsMainTitle->execute($productId)->willReturn(new AttributeCode('attribute_as_main_title_localizable_scopable'));
        $buildProductValues->buildForProductIdAndAttributeCodes($productId, ['attribute_as_main_title_localizable_scopable'])->willReturn([
            'attribute_as_main_title_localizable_scopable' => [
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
            ],
        ]);

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
            )
        )->shouldBeLike($expectedResult);
    }

    public function it_evaluates_title_with_suggestions(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        BuildProductValuesInterface $buildProductValues,
        GetAttributeAsMainTitleFromProductIdInterface $getAttributeAsMainTitle,
        TitleFormattingServiceInterface $titleFormattingService
    ) {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection(
            [
                'ecommerce' => ['en_US', 'fr_FR'],
            ]
        ));

        $productId = new ProductId(1);
        $getAttributeAsMainTitle->execute($productId)->willReturn(new AttributeCode('attribute_as_main_title_localizable_scopable'));
        $buildProductValues->buildForProductIdAndAttributeCodes($productId, ['attribute_as_main_title_localizable_scopable'])->willReturn([
            'attribute_as_main_title_localizable_scopable' => [
                'ecommerce' => [
                    'en_US' => 'Macbook Pro Retina 13" Azerty',
                    'fr_FR' => 'Titre non evalué',
                ],
            ],
        ]);

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
            )
        )->shouldBeLike($expectedResult);
    }

    public function it_evaluates_title_with_suggestions_with_two_en_locales(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        BuildProductValuesInterface $buildProductValues,
        GetAttributeAsMainTitleFromProductIdInterface $getAttributeAsMainTitle,
        TitleFormattingServiceInterface $titleFormattingService
    ) {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection(
            [
                'ecommerce' => ['en_US', 'en_GB'],
            ]
        ));

        $productId = new ProductId(1);
        $getAttributeAsMainTitle->execute($productId)->willReturn(new AttributeCode('attribute_as_main_title_localizable_scopable'));
        $buildProductValues->buildForProductIdAndAttributeCodes($productId, ['attribute_as_main_title_localizable_scopable'])->willReturn([
            'attribute_as_main_title_localizable_scopable' => [
                'ecommerce' => [
                    'en_US' => 'Macbook Pro Retina 13" Azerty',
                    'en_GB' => 'MacBook Pro Retina 13" Azerty',
                ],
            ],
        ]);

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
            )
        )->shouldBeLike($expectedResult);
    }
    public function it_evaluates_title_with_ignored_suggestions(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        BuildProductValuesInterface $buildProductValues,
        GetAttributeAsMainTitleFromProductIdInterface $getAttributeAsMainTitle,
        TitleFormattingServiceInterface $titleFormattingService,
        GetIgnoredProductTitleSuggestionQueryInterface $getIgnoredProductTitleSuggestionQuery
    ) {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection(
            [
                'ecommerce' => ['en_US'],
            ]
        ));

        $productId = new ProductId(1);
        $getAttributeAsMainTitle->execute($productId)->willReturn(new AttributeCode('attribute_as_main_title_localizable_scopable'));
        $buildProductValues->buildForProductIdAndAttributeCodes($productId, ['attribute_as_main_title_localizable_scopable'])->willReturn([
            'attribute_as_main_title_localizable_scopable' => [
                'ecommerce' => [
                    'en_US' => 'Macbook Pro Retina 13" Azerty',
                ],
            ],
        ]);

        $titleFormattingService->format(new ProductTitle('Macbook Pro Retina 13" Azerty'))->shouldBeCalled()->willReturn(new ProductTitle('MacBook Pro Retina 13" AZERTY'));

        $getIgnoredProductTitleSuggestionQuery->execute($productId, new ChannelCode('ecommerce'), new LocaleCode('en_US'))->shouldBeCalled()->willReturn('MacBook Pro Retina 13" AZERTY');

        $expectedResult = (new Write\CriterionEvaluationResult())
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(100))
            ->addStatus(new ChannelCode('ecommerce'), new LocaleCode('en_US'), CriterionEvaluationResultStatus::done());

        $this->evaluate(
            new Write\CriterionEvaluation(
                new CriterionEvaluationId(),
                new CriterionCode('criterion1'),
                new ProductId(1),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::pending()
            )
        )->shouldBeLike($expectedResult);
    }

    public function it_sets_status_in_error_when_a_title_formatting_fails(
        GetLocalesByChannelQueryInterface $localesByChannelQuery,
        BuildProductValuesInterface $buildProductValues,
        GetAttributeAsMainTitleFromProductIdInterface $getAttributeAsMainTitle,
        TitleFormattingServiceInterface $titleFormattingService
    ) {
        $localesByChannelQuery->getChannelLocaleCollection()->willReturn(new ChannelLocaleCollection([
            'mobile' => ['en_US', 'en_CA'],
        ]));

        $productId = new ProductId(1);
        $getAttributeAsMainTitle->execute($productId)->willReturn(new AttributeCode('attribute_as_main_title_localizable_scopable'));
        $buildProductValues->buildForProductIdAndAttributeCodes($productId, ['attribute_as_main_title_localizable_scopable'])->willReturn([
            'attribute_as_main_title_localizable_scopable' => [
                'mobile' => [
                    'en_US' => 'MacBook Pro Retina 13"',
                    'en_CA' => 'Fail',
                ],
            ],
        ]);

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
            )
        )->shouldBeLike($expectedResult);
    }
}
