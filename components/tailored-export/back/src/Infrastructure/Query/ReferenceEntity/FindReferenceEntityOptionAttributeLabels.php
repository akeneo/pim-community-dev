<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query\ReferenceEntity;

use Akeneo\Platform\TailoredExport\Domain\Query\FindReferenceEntityOptionAttributeLabelsInterface;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\FindOptionAttributeLabelsInterface;

class FindReferenceEntityOptionAttributeLabels implements FindReferenceEntityOptionAttributeLabelsInterface
{
    public function __construct(
        private FindOptionAttributeLabelsInterface $findOptionAttributeLabels,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $attributeIdentifier): array
    {
        return $this->findOptionAttributeLabels->find($attributeIdentifier);
    }
}
