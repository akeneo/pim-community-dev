<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Text;

use Akeneo\Pim\Automation\DataQualityInsights\Application\BuildProductValuesInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Text\EvaluateTitleFormatting;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Text\TitleFormattingServiceInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\GetProductAttributesCodesInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationId;
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
        GetProductAttributesCodesInterface $getProductAttributesCodes,
        TitleFormattingServiceInterface $titleFormattingService
    ) {
        $this->beConstructedWith($localesByChannelQuery, $buildProductValues, $getProductAttributesCodes, $titleFormattingService);
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(EvaluateTitleFormatting::class);
        $this->shouldImplement(EvaluateCriterionInterface::class);
    }

    public function it_returns_an_empty_rates_collection_when_a_product_has_no_attribute_has_main_title(
        $localesByChannelQuery,
        $buildProductValues,
        $getProductAttributesCodes
    ) {
        $localesByChannelQuery->execute()->willReturn(
            [
                'ecommerce' => ['en_US', 'fr_FR'],
                'mobile' => ['en_US', 'fr_FR'],
            ]
        );

        $productId = new ProductId(1);
        $getProductAttributesCodes->getTitle($productId)->willReturn([]);
        $buildProductValues->buildForProductIdAndAttributeCodes($productId, [])->willReturn([]);

        $this->evaluate(
            new CriterionEvaluation(
                new CriterionEvaluationId(),
                new CriterionCode('criterion1'),
                new ProductId(1),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::pending()
            )
        )->shouldBeLike(new CriterionEvaluationResult(new CriterionRateCollection(), ['attributes' => [], 'suggestions' => []]));
    }

    public function it_evaluates_title_with_no_suggestions(
        $localesByChannelQuery,
        $buildProductValues,
        $getProductAttributesCodes,
        $titleFormattingService
    ) {
        $localesByChannelQuery->execute()->willReturn(
            [
                'ecommerce' => ['en_US', 'fr_FR'],
                'mobile' => ['en_US', 'fr_FR'],
                'print' => ['en_US', 'fr_FR'],
            ]
        );

        $productId = new ProductId(1);
        $getProductAttributesCodes->getTitle($productId)->willReturn(['attribute_as_main_title_localizable_scopable']);
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
        $titleFormattingService->format(new ProductTitle('Titre non evalué'))->shouldNotBeCalled()->willReturn(new ProductTitle('Titre non evalué'));

        $rates = new CriterionRateCollection();
        $rates
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(100))
            ->addRate(new ChannelCode('mobile'), new LocaleCode('en_US'), new Rate(100))
            ->addRate(new ChannelCode('print'), new LocaleCode('en_US'), new Rate(100))
        ;

        $this->evaluate(
            new CriterionEvaluation(
                new CriterionEvaluationId(),
                new CriterionCode('criterion1'),
                new ProductId(1),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::pending()
            )
        )->shouldBeLike(new CriterionEvaluationResult($rates, [
            'attributes' => [],
            'suggestions' => []
        ]));
    }

    public function it_evaluates_title_with_suggestions(
        $localesByChannelQuery,
        $buildProductValues,
        $getProductAttributesCodes,
        $titleFormattingService
    ) {
        $localesByChannelQuery->execute()->willReturn(
            [
                'ecommerce' => ['en_US', 'fr_FR'],
            ]
        );


        $productId = new ProductId(1);
        $getProductAttributesCodes->getTitle($productId)->willReturn(['attribute_as_main_title_localizable_scopable']);
        $buildProductValues->buildForProductIdAndAttributeCodes($productId, ['attribute_as_main_title_localizable_scopable'])->willReturn([
            'attribute_as_main_title_localizable_scopable' => [
                'ecommerce' => [
                    'en_US' => 'Macbook Pro Retina 13" Azerty',
                    'fr_FR' => 'Titre non evalué',
                ],
            ],
        ]);

        $titleFormattingService->format(new ProductTitle('Macbook Pro Retina 13" Azerty'))->shouldBeCalled()->willReturn(new ProductTitle('MacBook Pro Retina 13" AZERTY'));
        $titleFormattingService->format(new ProductTitle('Titre non evalué'))->shouldNotBeCalled()->willReturn(new ProductTitle('Titre non evalué'));

        $rates = new CriterionRateCollection();
        $rates
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(76))
        ;

        $this->evaluate(
            new CriterionEvaluation(
                new CriterionEvaluationId(),
                new CriterionCode('criterion1'),
                new ProductId(1),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::pending()
            )
        )->shouldBeLike(new CriterionEvaluationResult($rates, [
            'attributes' => [
                'ecommerce' => [
                    'en_US' => ['attribute_as_main_title_localizable_scopable']
                ]
            ],
            'suggestions' => [
                'ecommerce' => [
                    'en_US' => 'MacBook Pro Retina 13" AZERTY'
                ]
            ]
        ]));
    }

    public function it_evaluates_title_with_suggestions_with_two_en_locales(
        $localesByChannelQuery,
        $buildProductValues,
        $getProductAttributesCodes,
        $titleFormattingService
    ) {
        $localesByChannelQuery->execute()->willReturn(
            [
                'ecommerce' => ['en_US', 'en_GB'],
            ]
        );

        $productId = new ProductId(1);
        $getProductAttributesCodes->getTitle($productId)->willReturn(['attribute_as_main_title_localizable_scopable']);
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

        $rates = new CriterionRateCollection();
        $rates
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(76))
            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_GB'), new Rate(88))
        ;

        $this->evaluate(
            new CriterionEvaluation(
                new CriterionEvaluationId(),
                new CriterionCode('criterion1'),
                new ProductId(1),
                new \DateTimeImmutable(),
                CriterionEvaluationStatus::pending()
            )
        )->shouldBeLike(new CriterionEvaluationResult($rates, [
            'attributes' => [
                'ecommerce' => [
                    'en_US' => ['attribute_as_main_title_localizable_scopable'],
                    'en_GB' => ['attribute_as_main_title_localizable_scopable']
                ]
            ],
            'suggestions' => [
                'ecommerce' => [
                    'en_US' => 'MacBook Pro Retina 13" AZERTY',
                    'en_GB' => 'MacBook Pro Retina 13" AZERTY'
                ]
            ]
        ]));
    }
}
