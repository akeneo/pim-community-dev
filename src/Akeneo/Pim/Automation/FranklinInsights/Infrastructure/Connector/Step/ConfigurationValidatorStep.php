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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Step;

use Akeneo\Tool\Bundle\BatchBundle\Item\Validator\ValidatorInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ConfigurationValidatorStep extends AbstractStep
{
    /** @var ValidatorInterface[] */
    private $validators;

    /**
     * @param $name
     * @param EventDispatcherInterface $eventDispatcher
     * @param JobRepositoryInterface $jobRepository
     * @param ValidatorInterface[] $validators
     */
    public function __construct(
        $name,
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        array $validators
    ) {
        parent::__construct($name, $eventDispatcher, $jobRepository);
        $this->validators = $validators;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(StepExecution $stepExecution): void
    {
        foreach ($this->validators as $validator) {
            $validator->validate(null);
        }

        $stepExecution->addSummaryInfo('configuration_validation', 'OK');
    }
}
