<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\EvaluateSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Text\EvaluateTitleFormatting;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Textarea\EvaluateUppercaseWords;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetLatestCriteriaEvaluationsByProductIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class GetProductEvaluation
{
    /**
     * @var GetLatestCriteriaEvaluationsByProductIdQueryInterface
     */
    private $getLatestCriteriaEvaluationsByProductIdQuery;

    public function __construct(
        GetLatestCriteriaEvaluationsByProductIdQueryInterface $getLatestCriteriaEvaluationsByProductIdQuery
    ) {
        $this->getLatestCriteriaEvaluationsByProductIdQuery = $getLatestCriteriaEvaluationsByProductIdQuery;
    }

    public function get(ProductId $productId): array
    {
        $productEvaluation = $this->getLatestCriteriaEvaluationsByProductIdQuery->execute($productId);

        return $this->adaptForFrontend( // @todo[DAPI-488] remove after adapting the frontend application with build response
            $this->build($productEvaluation)
        );
    }

    private function build(CriterionEvaluationCollection $evaluations): array
    {
        $evaluationsArray = iterator_to_array($evaluations->getIterator());

        $enrichmentCriteria = $this->filterByAxis($evaluationsArray, 'enrichment');
        $consistencyCriteria = $this->filterByAxis($evaluationsArray, 'consistency');

        return [
            'enrichment' => $this->buildAxisEvaluation($enrichmentCriteria),
            'consistency' => $this->buildAxisEvaluation($consistencyCriteria),
        ];
    }

    private function filterByAxis(array $evaluations, $filteredAxis)
    {
        return array_filter($evaluations, function (CriterionEvaluation $evaluation) use ($filteredAxis) {
            $currentAxis = $this->getAxis($evaluation->getCriterionCode());

            return $filteredAxis === $currentAxis;
        });
    }

    private function buildAxisEvaluation(array $axisEvaluations): array
    {
        return array_merge_recursive(
            $this->computeAxisRate($axisEvaluations),
            $this->computeAxisRecommendations($axisEvaluations),
            $this->computeAxisCriteriaRates($axisEvaluations)
        );
    }

    private function computeAxisRate(array $criteriaEvaluations): array
    {
        return array_reduce($criteriaEvaluations, function (array $previous, CriterionEvaluation $criterionEvaluation) {
            $rates = $criterionEvaluation->getResult()->getRates()->toArrayInt();

            $data = $this->compute($rates, $previous, function ($rate, $state, $channel, $locale) {
                $previousRate = $state[$channel][$locale]['rate']['value'] ?? 0;
                $previousTotal = $state[$channel][$locale]['rate']['total'] ?? 0;

                $newRate = $previousRate + intval($rate);
                $newTotal = $previousTotal + 1;

                return [
                    $channel => [
                        $locale => [
                            'rate' => [
                                'value' => $newRate,
                                'total' => $newTotal,
                                'average' => ($newRate / $newTotal)
                            ],
                        ]
                    ]
                ];
            });

            return array_merge(
                $previous,
                $data
            );
        }, []);
    }

    private function computeAxisCriteriaRates(array $criteriaEvaluations): array
    {
        return array_reduce($criteriaEvaluations, function (array $previous, CriterionEvaluation $criterionEvaluation) {
            $rates = $criterionEvaluation->getResult()->getRates()->toArrayInt();
            $criterion = strval($criterionEvaluation->getCriterionCode());

            $data = $this->compute($rates, $previous, function ($rate, $state, $channel, $locale) use ($criterion) {
                $numRate = intval($rate);
                $rate = new Rate($numRate);
                $letterRate = strval($rate);

                return [
                    $channel => [
                        $locale => [
                            'rates' => [
                                $criterion => [
                                    'criterion' => $criterion,
                                    'rate' => $numRate,
                                    'letterRate' => $letterRate
                                ]
                            ]
                        ]
                    ]
                ];
            });

            return array_merge_recursive(
                $previous,
                $data
            );
        }, []);
    }

    private function computeAxisRecommendations(array $criteriaEvaluations): array
    {
        return array_reduce($criteriaEvaluations, function (array $previous, CriterionEvaluation $criterionEvaluation) {
            $criterion = strval($criterionEvaluation->getCriterionCode());
            $evaluationData = $criterionEvaluation->getResult()->getData() ?? [];
            $recommendations = $evaluationData['attributes'] ?? [];

            $data = $this->compute($recommendations, $previous, function ($attributes, $state, $channel, $locale) use ($criterion) {
                $previousAttributes = $state[$channel][$locale]['recommendations'][$criterion] ?? [];

                $newAttributes = array_merge(
                    $previousAttributes,
                    $attributes
                );

                return [
                    $channel => [
                        $locale => [
                            'recommendations' => [
                                $criterion => [
                                    'criterion' => $criterion,
                                    'attributes' => $newAttributes,
                                ]
                            ]
                        ]
                    ]
                ];
            });

            return array_merge_recursive(
                $previous,
                $data
            );
        }, []);
    }

    private function getAxis(CriterionCode $code): ?string
    {
        $axes = [
            EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE => 'enrichment',
            EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE => 'enrichment',
            EvaluateUppercaseWords::CRITERION_CODE => 'consistency',
            EvaluateTitleFormatting::CRITERION_CODE => 'consistency',
            EvaluateSpelling::CRITERION_CODE => 'consistency',
        ];

        return $axes[strval($code)] ?? null;
    }

    private function compute(array $channels, array $state, callable $callback): array
    {
        $result = [];
        foreach ($channels as $channel => $locales) {
            foreach ($locales as $locale => $data) {
                $result = array_merge_recursive(
                    $result,
                    $callback($data, $state, $channel, $locale)
                );
            }
        }

        return $result;
    }

    /**
     * Adapt built data for frontend application
     * FROM:
     * <<<JSON
     * {
     *     enrichment: {
     *       ecommerce: {
     *         en_US: {
     *           rate: {
     *               value: 75,
     *               total: 3,
     *               average: 25
     *           },
     *           rates: {
     *             example_criterion_code: {criterion: 'example_criterion_code', rate: 25, letterRate: 'E'},
     *             ...
     *           },
     *           recommendations: {
     *             example_criterion_code: {criterion: 'example_criterion_code', attributes: ['attribute1', 'attribute2', 'attribute3']},
     *             ...
     *           },
     *         },
     *         ...
     *       },
     *       ...
     *     },
     *     consistency: {
     *       ecommerce: {
     *         en_US: {
     *           rate: {
     *               value: 75,
     *               total: 3,
     *               average: 25
     *           },
     *           rates: {
     *             example_criterion_code: {criterion: 'example_criterion_code', rate: 25, letterRate: 'E'},
     *             ...
     *           },
     *           recommendations: {
     *             example_criterion_code: {criterion: 'example_criterion_code', attributes: ['attribute1', 'attribute2', 'attribute3']},
     *             ...
     *           },
     *         },
     *         ...
     *       },
     *       ...
     *     }
     * }
     * JSON;
     *
     * TO:
     * <<<JSON
     * {
     *     enrichment: {
     *       ecommerce: {
     *         en_US: {
     *           rate: 'B',
     *           recommendations: [
     *             {criterion: 'example_criterion_code', attributes: ['attribute1', 'attribute2', 'attribute3']},
     *             ...
     *           ],
     *           rates: [
     *             {criterion: 'example_criterion_code', rate: 25, letterRate: 'E'},
     *             ...
     *           ],
     *         },
     *         ...
     *       },
     *       ...
     *     },
     *     consistency: {
     *       ecommerce: {
     *         en_US: {
     *           rate: 'D',
     *           recommendations: [
     *             {criterion: 'example_criterion_code', attributes: ['attribute1', 'attribute2', 'attribute3']},
     *             ...
     *           ],
     *         },
     *         ...
     *       },
     *       ...
     *     }
     * }
     * JSON;
     *
     * @param array $productEvaluation
     * @return array
     * @deprecated
     * @todo[DAPI-488] the design of the frontend data format is currently in progress. Remove this method after adapting the frontend application with build response
     */
    private function adaptForFrontend(array $productEvaluation): array
    {
        return array_map(function ($channels) {
            return $this->compute($channels, [], function ($axisEvaluation, $state, $channel, $locale) {
                $rate = $axisEvaluation['rate']['average'] ?? null;
                $recommendations = $axisEvaluation['recommendations'] ?? [];
                $rates = $axisEvaluation['rates'] ?? [];
                $stateRecommendations = $state[$channel][$locale]['recommendations'] ?? [];

                if ($rate !== null) {
                    $rate = (int) round($rate, 0, PHP_ROUND_HALF_DOWN);
                    $rate = new Rate($rate);
                    $rate = strval($rate);
                }

                $recommendations = array_merge(
                    $stateRecommendations,
                    $recommendations
                );

                return [
                    $channel => [
                        $locale => [
                            'rate' => $rate,
                            'recommendations' => array_values($recommendations),
                            'rates' => array_values($rates),
                        ]
                    ]
                ];
            });
        }, $productEvaluation);
    }
}
