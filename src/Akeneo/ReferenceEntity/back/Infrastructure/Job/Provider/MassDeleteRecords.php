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

namespace Akeneo\ReferenceEntity\Infrastructure\Job\Provider;

use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class MassDeleteRecords implements ConstraintCollectionProviderInterface, DefaultValuesProviderInterface
{
    public function getConstraintCollection()
    {
        return new Collection(
            [
                'fields' => [
                    'query' => new Callback(function ($value, ExecutionContextInterface $context) {
                        try {
                            RecordQuery::createFromNormalized($value);
                        } catch (\InvalidArgumentException $e) {
                            $context
                                ->buildViolation($e->getMessage())
                                ->addViolation();
                        }
                    }),
                    'reference_entity_identifier' => new Type(['type' => 'string']),
                    'user_to_notify' => new Type(['type' => 'string'])
                ],
                'allowMissingFields' => false,
            ]
        );
    }

    public function supports(JobInterface $job)
    {
        return 'reference_entity_mass_delete_records' === $job->getName();
    }

    public function getDefaultValues()
    {
        return [];
    }
}
