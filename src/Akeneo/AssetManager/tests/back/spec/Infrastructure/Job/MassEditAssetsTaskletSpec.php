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
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\AbstractEditValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditValueCommandFactoryInterface;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditValueCommandFactoryRegistryInterface;
use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\AssetManager\Domain\Repository\AssetIndexerInterface;
use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\AssetQueryBuilderInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MassEditAssetsTaskletSpec extends ObjectBehavior
{
    public function let(
        EditAssetHandler $editAssetsHandler,
        AssetQueryBuilderInterface $assetQueryBuilder,
        Client $assetClient,
        JobRepositoryInterface $jobRepository,
        AssetIndexerInterface $assetIndexer,
        JobStopper $jobStopper,
        EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier
    ) {
        $this->beConstructedWith(
            $editAssetsHandler,
            $assetQueryBuilder,
            $assetClient,
            $jobRepository,
            $assetIndexer,
            $jobStopper,
            $editValueCommandFactoryRegistry,
            $findAttributesIndexedByIdentifier,
            3
        );
    }

    function it_execute_mass_edit_of_assets(
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        AssetQueryBuilderInterface $assetQueryBuilder,
        Client $assetClient,
        JobStopper $jobStopper,
        AssetIndexerInterface $assetIndexer,
        EditAssetHandler $editAssetsHandler,
        EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        AbstractEditValueCommand $editValueAssetCommand,
        EditValueCommandFactoryInterface $editAssetCommandFactory,
        AbstractAttribute $attribute
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
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
        $jobParameters->get('updaters')->willReturn([
            [
                'attribute' => 'label_designer_d00de54460082b239164135175588647',
                'channel' => null,
                'locale' => 'fr_FR',
                'data' => 'My new data',
                'action' => 'replace',
            ]
        ]);

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

        $firstQuery = AssetQuery::createWithSearchAfter(
            $assetFamilyIdentifier,
            ChannelReference::createFromNormalized($normalizedQuery['channel']),
            LocaleReference::createFromNormalized($normalizedQuery['locale']),
            3,
            null,
            $normalizedQuery['filters']
        );

        $findAttributesIndexedByIdentifier->find($assetFamilyIdentifier)->willReturn([
            'label_designer_d00de54460082b239164135175588647' => $attribute->getWrappedObject()
        ]);

        $assetQueryBuilder->buildFromQuery($firstQuery, 'code')->willReturn($firstElasticSearchQuery);
        $assetClient->search($firstElasticSearchQuery)->willReturn([
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
                    ],
                ],
            ],
        ]);

        $secondQuery = AssetQuery::createNextWithSearchAfter($firstQuery, AssetCode::fromString('awesome'));
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

        $editValueCommandFactoryRegistry
            ->getFactory($attribute,  [
                'attribute' => 'label_designer_d00de54460082b239164135175588647',
                'channel' => null,
                'locale' => 'fr_FR',
                'data' => 'My new data',
                'action' => 'replace',
            ])
            ->shouldBeCalled()
            ->willReturn($editAssetCommandFactory);

        $editAssetCommandFactory->create($attribute, [
            'attribute' => 'label_designer_d00de54460082b239164135175588647',
            'channel' => null,
            'locale' => 'fr_FR',
            'data' => 'My new data',
            'action' => 'replace',
        ])->shouldBeCalled()->willReturn($editValueAssetCommand);

        $editValueAssetCommands = [$editValueAssetCommand->getWrappedObject()];
        $editAssetCommand1 = new EditAssetCommand('packshot', 'nice', $editValueAssetCommands);
        $editAssetCommand2 = new EditAssetCommand('packshot', 'cool', $editValueAssetCommands);
        $editAssetCommand3 = new EditAssetCommand('packshot', 'AWESOME', $editValueAssetCommands);

        $editAssetsHandler->__invoke($editAssetCommand1)->shouldBeCalledOnce();
        $editAssetsHandler->__invoke($editAssetCommand2)->shouldBeCalledOnce();
        $editAssetsHandler->__invoke($editAssetCommand3)->shouldBeCalledOnce();

        $assetIndexer->refresh()->shouldBeCalled();

        $this->execute();
    }

    function it_batch_mass_edit_of_assets(
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        AssetQueryBuilderInterface $assetQueryBuilder,
        Client $assetClient,
        JobStopper $jobStopper,
        AssetIndexerInterface $assetIndexer,
        EditAssetHandler $editAssetsHandler,
        EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        AbstractEditValueCommand $editValueAssetCommand,
        EditValueCommandFactoryInterface $editAssetCommandFactory,
        AbstractAttribute $attribute
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
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

        $jobParameters->get('updaters')->willReturn([
            [
                'attribute' => 'label_designer_d00de54460082b239164135175588647',
                'channel' => null,
                'locale' => 'fr_FR',
                'data' => 'My new data',
                'action' => 'replace',
            ]
        ]);

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

        $findAttributesIndexedByIdentifier->find($assetFamilyIdentifier)->willReturn([
            'label_designer_d00de54460082b239164135175588647' => $attribute->getWrappedObject()
        ]);

        $firstQuery = AssetQuery::createWithSearchAfter(
            $assetFamilyIdentifier,
            ChannelReference::createFromNormalized($normalizedQuery['channel']),
            LocaleReference::createFromNormalized($normalizedQuery['locale']),
            3,
            null,
            $normalizedQuery['filters']
        );
        $assetQueryBuilder->buildFromQuery($firstQuery, 'code')->willReturn($firstElasticSearchQuery);
        $assetClient->search($firstElasticSearchQuery)->willReturn([
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

        $secondQuery = AssetQuery::createNextWithSearchAfter($firstQuery, AssetCode::fromString('awesome'));
        $secondElasticSearchQuery = array_merge($firstElasticSearchQuery, ['search_after' => 'awesome']);
        $assetQueryBuilder->buildFromQuery($secondQuery, 'code')->willReturn($secondElasticSearchQuery);
        $assetClient->search($secondElasticSearchQuery)->willReturn([
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


        $thirdQuery = AssetQuery::createNextWithSearchAfter($secondQuery, AssetCode::fromString('tricky'));
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

        $editValueCommandFactoryRegistry
            ->getFactory($attribute,  [
                'attribute' => 'label_designer_d00de54460082b239164135175588647',
                'channel' => null,
                'locale' => 'fr_FR',
                'data' => 'My new data',
                'action' => 'replace',
            ])
            ->shouldBeCalled()
            ->willReturn($editAssetCommandFactory);

        $editAssetCommandFactory->create($attribute, [
            'attribute' => 'label_designer_d00de54460082b239164135175588647',
            'channel' => null,
            'locale' => 'fr_FR',
            'data' => 'My new data',
            'action' => 'replace',
        ])->shouldBeCalled()->willReturn($editValueAssetCommand);

        $editValueAssetCommands = [$editValueAssetCommand->getWrappedObject()];
        $editAssetCommand1 = new EditAssetCommand('packshot', 'nice', $editValueAssetCommands);
        $editAssetCommand2 = new EditAssetCommand('packshot', 'cool', $editValueAssetCommands);
        $editAssetCommand3 = new EditAssetCommand('packshot', 'AWESOME', $editValueAssetCommands);

        $editAssetsHandler->__invoke($editAssetCommand1)->shouldBeCalledOnce();
        $editAssetsHandler->__invoke($editAssetCommand2)->shouldBeCalledOnce();
        $editAssetsHandler->__invoke($editAssetCommand3)->shouldBeCalledOnce();

        $stepExecution->incrementProcessedItems(1)->shouldBeCalledOnce();
        $stepExecution->incrementSummaryInfo('assets', 1)->shouldBeCalledOnce();

        $editAssetCommand4 = new EditAssetCommand('packshot', 'tricky', $editValueAssetCommands);
        $editAssetsHandler->__invoke($editAssetCommand4)->shouldBeCalledOnce();

        $assetIndexer->refresh()->shouldBeCalled();

        $this->execute();
    }
}
