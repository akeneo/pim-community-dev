<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributesMappingByFamilyHandler;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class RemoveAttributeFromAttributeMappingTasklet implements TaskletInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var GetAttributesMappingByFamilyHandler */
    private $getAttributesMappingHandler;

    /** @var UpdateAttributesMappingByFamilyHandler */
    private $updateAttributesMappingHandler;

    public function __construct(
        GetAttributesMappingByFamilyHandler $getAttributesMappingHandler,
        UpdateAttributesMappingByFamilyHandler $updateAttributesMappingHandler
    ) {
        $this->getAttributesMappingHandler = $getAttributesMappingHandler;
        $this->updateAttributesMappingHandler = $updateAttributesMappingHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }
}
