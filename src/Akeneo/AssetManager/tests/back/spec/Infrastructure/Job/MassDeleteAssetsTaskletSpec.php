<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Application\Asset\DeleteAssets\DeleteAssetsHandler;
use Akeneo\AssetManager\Domain\Repository\AssetIndexerInterface;
use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\AssetQueryBuilderInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
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
}
