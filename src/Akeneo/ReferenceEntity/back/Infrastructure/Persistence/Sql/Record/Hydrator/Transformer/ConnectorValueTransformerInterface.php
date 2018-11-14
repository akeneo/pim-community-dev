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

/**
 * Transform a normalized record value to a normalized record value for connector.
 *
 * The transformation returns null if the value is irrelevant (e.g. a record that doesn't exists for a record type value)
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
interface ConnectorValueTransformerInterface
{
    public function supports(AbstractAttribute $attribute): bool;

    public function transform(array $normalizedValue, AbstractAttribute $attribute): ?array;
}
