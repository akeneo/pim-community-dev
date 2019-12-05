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

use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\PushStructureAndProductsToFranklin;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Symfony\Component\Validator\Constraints;

final class PushStructureAndProductsToFranklinParameters implements ConstraintCollectionProviderInterface, DefaultValuesProviderInterface
{
    const ATTRIBUTES_BATCH_SIZE = 'attributes_batch_size';
    const FAMILIES_BATCH_SIZE = 'families_batch_size';
    const PRODUCTS_BATCH_SIZE = 'products_batch_size';

    /**
     * @inheritDoc
     */
    public function getConstraintCollection()
    {
        return new Constraints\Collection(
            [
                'fields' => [
                    self::ATTRIBUTES_BATCH_SIZE => new Constraints\Type('int'),
                    self::FAMILIES_BATCH_SIZE => new Constraints\Type('int'),
                    self::PRODUCTS_BATCH_SIZE => new Constraints\Type('int'),
                ],
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function supports(JobInterface $job)
    {
        return JobInstanceNames::PUSH_STRUCTURE_AND_PRODUCTS === $job->getName();
    }

    /**
     * @inheritDoc
     */
    public function getDefaultValues()
    {
        return [
            self::ATTRIBUTES_BATCH_SIZE => PushStructureAndProductsToFranklin::DEFAULT_ATTRIBUTES_BATCH_SIZE,
            self::FAMILIES_BATCH_SIZE => PushStructureAndProductsToFranklin::DEFAULT_FAMILIES_BATCH_SIZE,
            self::PRODUCTS_BATCH_SIZE => PushStructureAndProductsToFranklin::DEFAULT_PRODUCTS_BATCH_SIZE,
        ];
    }
}
