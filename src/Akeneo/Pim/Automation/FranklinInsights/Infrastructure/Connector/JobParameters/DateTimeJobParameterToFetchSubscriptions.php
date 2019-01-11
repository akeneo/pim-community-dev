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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobParameters;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\SelectLastCompletedFetchExecutionDatetimeQuery;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class DateTimeJobParameterToFetchSubscriptions implements ConstraintCollectionProviderInterface, DefaultValuesProviderInterface
{
    /** @var SelectLastCompletedFetchExecutionDatetimeQuery */
    private $selectLastCompletedFetchExecutionDatetimeQuery;

    /**
     * @param SelectLastCompletedFetchExecutionDatetimeQuery $selectLastCompletedFetchExecutionDatetimeQuery
     */
    public function __construct(
        SelectLastCompletedFetchExecutionDatetimeQuery $selectLastCompletedFetchExecutionDatetimeQuery
    ) {
        $this->selectLastCompletedFetchExecutionDatetimeQuery = $selectLastCompletedFetchExecutionDatetimeQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection()
    {
        return new Collection([
            'updated_since' => new DateTime(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return JobInstanceNames::FETCH_PRODUCTS === $job->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValues()
    {
        $lastExecutionDatetime = $this->selectLastCompletedFetchExecutionDatetimeQuery->execute();
        if (null === $lastExecutionDatetime) {
            $lastExecutionDatetime = '2013-01-01';
        }
        $datetime = new \DateTime($lastExecutionDatetime, new \DateTimeZone('UTC'));
        $datetime->add(new \DateInterval('PT1H'));

        return [
            'updated_since' => $datetime,
         ];
    }
}
