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
 * Find record codes for given identifiers
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
interface FindCodesByIdentifiersInterface
{
    /**
     * Return records codes for given $identifiers, indexed by identifier, eg:
     *
     * [
     *     'designer_starck_abcdef123456789' => 'starck',
     *     'designer_dyson_abcdef123456789' => 'dyson',
     * ]
     */
    public function find(array $identifiers): array;
}
