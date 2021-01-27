<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Application\Asset\DeleteAssets\DeleteAssetsCommand;
use Akeneo\AssetManager\Application\Asset\DeleteAssets\DeleteAssetsHandler;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Repository\AssetIndexerInterface;
use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\AssetQueryBuilderInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class MassDeleteAssetsTaskletSpec extends ObjectBehavior
{
    public function let(
        DeleteAssetsHandler $deleteAssetsHandler,
        AssetQueryBuilderInterface $assetQueryBuilder,
        Client $assetClient,
        JobRepositoryInterface $jobRepository,
        AssetIndexerInterface $assetIndexer,
        JobStopper $jobStopper
    ) {
        $this->beConstructedWith(
            $deleteAssetsHandler,
            $assetQueryBuilder,
            $assetClient,
            $jobRepository,
            $assetIndexer,
            $jobStopper,
            3
        );
    }

    function it_execute_mass_delete_of_assets(
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        AssetQueryBuilderInterface $assetQueryBuilder,
        Client $assetClient,
        JobStopper $jobStopper,
        AssetIndexerInterface $assetIndexer,
        DeleteAssetsHandler $deleteAssetsHandler
    ) {
        $normalizedQuery = [
            "page" => 0,
            "size" => 3,
            "locale" => "en_US",
            "channel" => "ecommerce",
            "filters" => [
                [
                    "field" => "asset_family",
                    "value" => "packshot",
                    "context" => [],
                    "operator" => "="
                ],
                [
                    "field" => "code",
                    "value" => ["packshot_1"],
                    "context" => [],
                    "operator" => "NOT IN"
                ],
            ]
        ];

        $this->setStepExecution($stepExecution);
        $jobStopper->isStopping($stepExecution)->willReturn(false);
        $jobParameters->get('asset_family_identifier')->willReturn('packshot');
        $jobParameters->get('query')->willReturn($normalizedQuery);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $firstElasticSearchQuery = [
            '_source' => 'code',
            'size'    => 3,
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'term' => [
                                        'asset_family_code' => 'packshot',
                                    ],
                                ],
                            ],
                            'must_not' => [
                                [
                                    'terms' => [
                                        'code' => 'packshot_1',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'track_total_hits' => true,
        ];

        $firstQuery = AssetQuery::createFromNormalized($normalizedQuery);
        $assetQueryBuilder->buildFromQuery($firstQuery, 'code')->willReturn($firstElasticSearchQuery);
        $assetClient->search($firstElasticSearchQuery)->willReturn([
            'hits' => [
                'total' => ['value' => 3],
                'hits' => [
                    [
                        '_source' => ['code' => 'nice'],
                    ],
                    [
                        '_source' => ['code' => 'cool'],
                    ],
                    [
                        '_source' => ['code' => 'awesome'],
                    ]
                ]
            ]
        ]);

        $secondQuery = AssetQuery::createNextQuery($firstQuery, AssetCode::fromString('awesome'));
        $secondElasticSearchQuery = array_merge($firstElasticSearchQuery, ['search_after' => 'awesome']);
        $assetQueryBuilder->buildFromQuery($secondQuery, 'code')->willReturn($secondElasticSearchQuery);
        $assetClient->search($secondElasticSearchQuery)->willReturn([
            'hits' => [
                'total' => ['value' => 3],
                'hits' => []
            ]
        ]);

        $stepExecution->setTotalItems(3)->shouldBeCalled();
        $stepExecution->incrementProcessedItems(3)->shouldBeCalledOnce();
        $stepExecution->incrementSummaryInfo('assets', 3)->shouldBeCalledOnce();

        $deleteAssetsHandler
            ->__invoke(new DeleteAssetsCommand('packshot', ['nice', 'cool', 'awesome']))
            ->shouldBeCalled();

        $assetIndexer->refresh()->shouldBeCalled();

        $this->execute();
    }

    function it_batch_mass_delete_of_assets(
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        AssetQueryBuilderInterface $assetQueryBuilder,
        Client $assetClient,
        JobStopper $jobStopper,
        AssetIndexerInterface $assetIndexer,
        DeleteAssetsHandler $deleteAssetsHandler
    ) {
        $normalizedQuery = [
            "page" => 0,
            "size" => 3,
            "locale" => "en_US",
            "channel" => "ecommerce",
            "filters" => [
                [
                    "field" => "asset_family",
                    "value" => "packshot",
                    "context" => [],
                    "operator" => "="
                ],
                [
                    "field" => "code",
                    "value" => ["packshot_1"],
                    "context" => [],
                    "operator" => "NOT IN"
                ],
            ]
        ];

        $this->setStepExecution($stepExecution);
        $jobStopper->isStopping($stepExecution)->willReturn(false);
        $jobParameters->get('asset_family_identifier')->willReturn('packshot');
        $jobParameters->get('query')->willReturn($normalizedQuery);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $firstElasticSearchQuery = [
            '_source' => 'code',
            'size'    => 3,
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'term' => [
                                        'asset_family_code' => 'packshot',
                                    ],
                                ],
                            ],
                            'must_not' => [
                                [
                                    'terms' => [
                                        'code' => 'packshot_1',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'track_total_hits' => true,
        ];

        $firstQuery = AssetQuery::createFromNormalized($normalizedQuery);
        $assetQueryBuilder->buildFromQuery($firstQuery, 'code')->willReturn($firstElasticSearchQuery);
        $assetClient->search($firstElasticSearchQuery)->willReturn([
            'hits' => [
                'total' => ['value' => 4],
                'hits' => [
                    [
                        '_source' => ['code' => 'nice'],
                    ],
                    [
                        '_source' => ['code' => 'cool'],
                    ],
                    [
                        '_source' => ['code' => 'awesome'],
                    ]
                ],
            ],
        ]);

        $secondQuery = AssetQuery::createNextQuery($firstQuery, AssetCode::fromString('awesome'));
        $secondElasticSearchQuery = array_merge($firstElasticSearchQuery, ['search_after' => 'awesome']);
        $assetQueryBuilder->buildFromQuery($secondQuery, 'code')->willReturn($secondElasticSearchQuery);
        $assetClient->search($secondElasticSearchQuery)->willReturn([
            'hits' => [
                'total' => ['value' => 4],
                'hits' => [
                    [
                        '_source' => ['code' => 'tricky'],
                    ],
                ],
            ],
        ]);


        $thirdQuery = AssetQuery::createNextQuery($secondQuery, AssetCode::fromString('tricky'));
        $thirdElasticSearchQuery = array_merge($firstElasticSearchQuery, ['search_after' => 'tricky']);
        $assetQueryBuilder->buildFromQuery($thirdQuery, 'code')->willReturn($thirdElasticSearchQuery);
        $assetClient->search($thirdElasticSearchQuery)->willReturn([
            'hits' => [
                'total' => ['value' => 4],
                'hits' => []
            ]
        ]);

        $stepExecution->setTotalItems(4)->shouldBeCalled();
        $stepExecution->incrementProcessedItems(3)->shouldBeCalledOnce();
        $stepExecution->incrementSummaryInfo('assets', 3)->shouldBeCalledOnce();
        $deleteAssetsHandler
            ->__invoke(new DeleteAssetsCommand('packshot', ['nice', 'cool', 'awesome']))
            ->shouldBeCalled();


        $stepExecution->incrementProcessedItems(1)->shouldBeCalledOnce();
        $stepExecution->incrementSummaryInfo('assets', 1)->shouldBeCalledOnce();
        $deleteAssetsHandler
            ->__invoke(new DeleteAssetsCommand('packshot', ['tricky']))
            ->shouldBeCalled();

        $assetIndexer->refresh()->shouldBeCalled();

        $this->execute();
    }
}
