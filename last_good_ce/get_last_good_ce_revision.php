<?php

/**
 * This script tries to find the most recent successful
 * CE master build and return the associated commit hash
 */

use Jmleroux\CircleCi\Api\Pipeline\AllPipelines;
use Jmleroux\CircleCi\Api\Pipeline\PipelineWorkflows;
use Jmleroux\CircleCi\Api\Workflow\WorkflowJobs;
use Jmleroux\CircleCi\Client;

require_once __DIR__.'/vendor/autoload.php';

$maxPipelinesToCheck = 10;

$circleCiToken = getenv('CIRCLECI_API_TOKEN');

$client = new Client($circleCiToken, 'v2');
$query = new AllPipelines($client);
$pipelineWorkflows = new PipelineWorkflows($client);

$pipelines = $query->execute('gh/akeneo/pim-community-dev', $maxPipelinesToCheck, 'master');
$workflows = [];

$commitHash = null;

$pipelineIterator = new \ArrayIterator($pipelines);

while (null === $commitHash && $pipelineIterator->valid()) {
    $pipeline = $pipelineIterator->current();

    $workflows = $pipelineWorkflows->execute($pipeline->id());

    $workflowIterator = new \ArrayIterator($workflows);

    while (null === $commitHash && $workflowIterator->valid()) {
        $workflow = $workflowIterator->current();

        if ($workflow->status() === 'success') {
            $commitHash = $pipeline->vcs()->revision;
        }

        $workflowIterator->next();
    }

    $pipelineIterator->next();
}

if (null === $commitHash) {
    echo "No successful revision found on the last execution";
    exit (1);
} else {
    echo $commitHash."\n";

    exit (0);
}
