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

use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;

/**
 * Find a list of activated locale identifiers from a given list of locale identifiers.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
interface FindActivatedLocalesByIdentifiersInterface
{
    public function __invoke(LocaleIdentifierCollection $localeIdentifiers): LocaleIdentifierCollection;
}
