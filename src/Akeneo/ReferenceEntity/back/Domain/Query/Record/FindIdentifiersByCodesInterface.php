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

namespace Akeneo\ReferenceEntity\Domain\Query\Record;

/**
 * Find record identifiers for given codes
 *
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
interface FindIdentifiersByCodesInterface
{
    /**
     * Return records identifier for given codes, eg:
     *
     * [
     *     'designer_starck_abcdef123456789',
     *     'designer_dyson_abcdef123456789',
     * ]
     */
    public function find(string $referenceEntity, array $codes): array;
}
