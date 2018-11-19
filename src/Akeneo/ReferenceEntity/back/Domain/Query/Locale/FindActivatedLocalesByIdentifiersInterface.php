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

namespace Akeneo\ReferenceEntity\Domain\Query\Locale;

use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;

/**
 * Find a list of activated locale codes by their identifiers.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
interface FindActivatedLocalesByIdentifiersInterface
{
    /**
     * @param LocaleIdentifier[] $localeIdentifiers
     *
     * @return string[]
     */
    public function __invoke(array $localeIdentifiers): array;
}
