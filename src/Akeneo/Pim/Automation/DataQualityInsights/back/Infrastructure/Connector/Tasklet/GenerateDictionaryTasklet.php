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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\DictionaryGenerator;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\DictionarySource;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Psr\Log\LoggerInterface;

final class GenerateDictionaryTasklet implements TaskletInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var DictionaryGenerator */
    private $dictionaryGenerator;

    /** @var DictionarySource */
    private $dictionarySource;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(DictionaryGenerator $dictionaryGenerator, DictionarySource $dictionarySource, LoggerInterface $logger)
    {
        $this->dictionaryGenerator = $dictionaryGenerator;
        $this->dictionarySource = $dictionarySource;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $this->dictionaryGenerator->generate($this->dictionarySource);
        } catch (\Exception $exception) {
            $this->stepExecution->addFailureException($exception);
            $this->logger->error('Generate Data-Quality-Insights dictionary failed', [
                'step_execution_id' => $this->stepExecution->getId(),
                'message' => $exception->getMessage()
            ]);
        }
    }
}
