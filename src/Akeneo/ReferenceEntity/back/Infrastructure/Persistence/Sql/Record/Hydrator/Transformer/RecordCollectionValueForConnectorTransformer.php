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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\Transformer;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordCollectionValueForConnectorTransformer implements ValueForConnectorTransformerInterface
{
    public function supports(AbstractAttribute $attribute): bool
    {
        return $attribute instanceof RecordCollectionAttribute;
    }

    public function transform(array $normalizedValue): array
    {
        return [
            'locale'  => $normalizedValue['locale'] ?? null,
            'channel' => $normalizedValue['channel'] ?? null,
            'data'    => $normalizedValue['data'] ?? [],
        ];
    }
}
