<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Job;

use Akeneo\ReferenceEntity\Application\Record\DeleteRecords\DeleteRecordsCommand;
use Akeneo\ReferenceEntity\Application\Record\DeleteRecords\DeleteRecordsHandler;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Domain\Repository\RecordIndexerInterface;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\RecordQueryBuilderInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class MassDeleteRecordsTaskletSpec extends ObjectBehavior
{
    public function let(
        DeleteRecordsHandler $deleteRecordsHandler,
        RecordQueryBuilderInterface $recordQueryBuilder,
        Client $recordClient,
        JobRepositoryInterface $jobRepository,
        RecordIndexerInterface $recordIndexer,
        JobStopper $jobStopper
    ) {
        $this->beConstructedWith(
            $deleteRecordsHandler,
            $recordQueryBuilder,
            $recordClient,
            $jobRepository,
            $recordIndexer,
            $jobStopper,
            3
        );
    }

    function it_execute_mass_delete_of_records(
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        RecordQueryBuilderInterface $recordQueryBuilder,
        Client $recordClient,
        JobStopper $jobStopper,
        RecordIndexerInterface $recordIndexer,
        DeleteRecordsHandler $deleteRecordsHandler
    ) {
        $normalizedQuery = [
            "page" => 0,
            "size" => 3,
            "locale" => "en_US",
            "channel" => "ecommerce",
            "filters" => [
                [
                    "field" => "reference_entity",
                    "value" => "brand",
                    "context" => [],
                    "operator" => "="
                ],
                [
                    "field" => "code",
                    "value" => ["brand_1"],
                    "context" => [],
                    "operator" => "NOT IN"
                ],
            ]
        ];

        $this->setStepExecution($stepExecution);
        $jobStopper->isStopping($stepExecution)->willReturn(false);
        $jobParameters->get('reference_entity_identifier')->willReturn('brand');
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
                                        'reference_entity_code' => 'brand',
                                    ],
                                ],
                            ],
                            'must_not' => [
                                [
                                    'terms' => [
                                        'code' => 'brand_1',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'track_total_hits' => true,
        ];

        $firstQuery = RecordQuery::createWithSearchAfter(
            ReferenceEntityIdentifier::fromString('brand'),
            ChannelReference::createFromNormalized($normalizedQuery['channel']),
            LocaleReference::createFromNormalized($normalizedQuery['locale']),
            3,
            null,
            $normalizedQuery['filters']
        );
        $recordQueryBuilder->buildFromQuery($firstQuery, 'code')->willReturn($firstElasticSearchQuery);
        $recordClient->search($firstElasticSearchQuery)->willReturn([
            'hits' => [
                'total' => ['value' => 3],
                'hits' => [
                    [
                        '_source' => ['code' => 'nice'],
                        'sort' => ['nice'],
                    ],
                    [
                        '_source' => ['code' => 'cool'],
                        'sort' => ['cool'],
                    ],
                    [
                        '_source' => ['code' => 'AWESOME'],
                        'sort' => ['awesome'],
                    ]
                ]
            ]
        ]);

        $secondQuery = RecordQuery::createNextWithSearchAfter($firstQuery, RecordCode::fromString('awesome'));
        $secondElasticSearchQuery = array_merge($firstElasticSearchQuery, ['search_after' => 'awesome']);
        $recordQueryBuilder->buildFromQuery($secondQuery, 'code')->willReturn($secondElasticSearchQuery);
        $recordClient->search($secondElasticSearchQuery)->willReturn([
            'hits' => [
                'total' => ['value' => 3],
                'hits' => []
            ]
        ]);

        $stepExecution->setTotalItems(3)->shouldBeCalled();
        $stepExecution->incrementProcessedItems(3)->shouldBeCalledOnce();
        $stepExecution->incrementSummaryInfo('records', 3)->shouldBeCalledOnce();

        $deleteRecordsHandler
            ->__invoke(new DeleteRecordsCommand('brand', ['nice', 'cool', 'AWESOME']))
            ->shouldBeCalled();

        $recordIndexer->refresh()->shouldBeCalled();

        $this->execute();
    }

    function it_batch_mass_delete_of_records(
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        RecordQueryBuilderInterface $recordQueryBuilder,
        Client $recordClient,
        JobStopper $jobStopper,
        RecordIndexerInterface $recordIndexer,
        DeleteRecordsHandler $deleteRecordsHandler
    ) {
        $normalizedQuery = [
            "page" => 0,
            "size" => 3,
            "locale" => "en_US",
            "channel" => "ecommerce",
            "filters" => [
                [
                    "field" => "reference_entity",
                    "value" => "brand",
                    "context" => [],
                    "operator" => "="
                ],
                [
                    "field" => "code",
                    "value" => ["brand_1"],
                    "context" => [],
                    "operator" => "NOT IN"
                ],
            ]
        ];

        $this->setStepExecution($stepExecution);
        $jobStopper->isStopping($stepExecution)->willReturn(false);
        $jobParameters->get('reference_entity_identifier')->willReturn('brand');
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
                                        'reference_entity_code' => 'brand',
                                    ],
                                ],
                            ],
                            'must_not' => [
                                [
                                    'terms' => [
                                        'code' => 'brand_1',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'track_total_hits' => true,
        ];

        $firstQuery = RecordQuery::createWithSearchAfter(
            ReferenceEntityIdentifier::fromString('brand'),
            ChannelReference::createFromNormalized($normalizedQuery['channel']),
            LocaleReference::createFromNormalized($normalizedQuery['locale']),
            3,
            null,
            $normalizedQuery['filters']
        );
        $recordQueryBuilder->buildFromQuery($firstQuery, 'code')->willReturn($firstElasticSearchQuery);
        $recordClient->search($firstElasticSearchQuery)->willReturn([
            'hits' => [
                'total' => ['value' => 4],
                'hits' => [
                    [
                        '_source' => ['code' => 'nice'],
                        'sort' => ['nice'],
                    ],
                    [
                        '_source' => ['code' => 'cool'],
                        'sort' => ['cool'],
                    ],
                    [
                        '_source' => ['code' => 'AWESOME'],
                        'sort' => ['awesome'],
                    ]
                ],
            ],
        ]);

        $secondQuery = RecordQuery::createNextWithSearchAfter($firstQuery, RecordCode::fromString('awesome'));
        $secondElasticSearchQuery = array_merge($firstElasticSearchQuery, ['search_after' => 'awesome']);
        $recordQueryBuilder->buildFromQuery($secondQuery, 'code')->willReturn($secondElasticSearchQuery);
        $recordClient->search($secondElasticSearchQuery)->willReturn([
            'hits' => [
                'total' => ['value' => 4],
                'hits' => [
                    [
                        '_source' => ['code' => 'tricky'],
                        'sort' => ['tricky'],
                    ],
                ],
            ],
        ]);


        $thirdQuery = RecordQuery::createNextWithSearchAfter($secondQuery, RecordCode::fromString('tricky'));
        $thirdElasticSearchQuery = array_merge($firstElasticSearchQuery, ['search_after' => 'tricky']);
        $recordQueryBuilder->buildFromQuery($thirdQuery, 'code')->willReturn($thirdElasticSearchQuery);
        $recordClient->search($thirdElasticSearchQuery)->willReturn([
            'hits' => [
                'total' => ['value' => 4],
                'hits' => []
            ]
        ]);

        $stepExecution->setTotalItems(4)->shouldBeCalled();
        $stepExecution->incrementProcessedItems(3)->shouldBeCalledOnce();
        $stepExecution->incrementSummaryInfo('records', 3)->shouldBeCalledOnce();
        $deleteRecordsHandler
            ->__invoke(new DeleteRecordsCommand('brand', ['nice', 'cool', 'AWESOME']))
            ->shouldBeCalled();


        $stepExecution->incrementProcessedItems(1)->shouldBeCalledOnce();
        $stepExecution->incrementSummaryInfo('records', 1)->shouldBeCalledOnce();
        $deleteRecordsHandler
            ->__invoke(new DeleteRecordsCommand('brand', ['tricky']))
            ->shouldBeCalled();

        $recordIndexer->refresh()->shouldBeCalled();

        $this->execute();
    }
}
