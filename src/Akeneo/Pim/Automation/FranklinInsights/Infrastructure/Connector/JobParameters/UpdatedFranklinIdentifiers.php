<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobParameters;

use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class UpdatedFranklinIdentifiers implements ConstraintCollectionProviderInterface, DefaultValuesProviderInterface
{
    /**
     * @return Collection
     */
    public function getConstraintCollection()
    {
        return new Collection(
            [
                'fields' => [
                    'updated_identifiers' => [
                        new Type('array'),
                        new NotBlank(),
                        new Choice([
                            'choices' => IdentifiersMapping::FRANKLIN_IDENTIFIERS,
                            'strict' => true,
                            'multiple' => true,
                        ]),
                    ],
                ],
            ]
        );
    }

    /**
     * @param JobInterface $job
     *
     * @return bool
     */
    public function supports(JobInterface $job)
    {
        return JobInstanceNames::IDENTIFY_PRODUCTS_TO_RESUBSCRIBE == $job->getName();
    }

    /**
     * @return array
     */
    public function getDefaultValues()
    {
        return [
            'updated_identifiers' => [],
        ];
    }
}
