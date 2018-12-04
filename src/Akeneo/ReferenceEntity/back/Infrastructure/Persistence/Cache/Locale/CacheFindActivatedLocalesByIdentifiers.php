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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Cache\Locale;

use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Query\Locale\FindActivatedLocalesByIdentifiersInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CacheFindActivatedLocalesByIdentifiers implements FindActivatedLocalesByIdentifiersInterface
{
    /** @var FindActivatedLocalesByIdentifiersInterface */
    private $findActivatedLocalesByIdentifiers;

    /** @var LocaleIdentifierCollection[] */
    private $activatedLocales;

    public function __construct(FindActivatedLocalesByIdentifiersInterface $findActivatedLocalesByIdentifiers)
    {
        $this->findActivatedLocalesByIdentifiers = $findActivatedLocalesByIdentifiers;
        $this->activatedLocales = [];
    }

    public function __invoke(LocaleIdentifierCollection $localeIdentifiers): LocaleIdentifierCollection
    {
        $localeIdentifiersKey = array_reduce(iterator_to_array($localeIdentifiers), function ($key, $localIdentifier) {
            $key .= $localIdentifier->normalize();

            return $key;
        }, '');

        if (!array_key_exists($localeIdentifiersKey, $this->activatedLocales)) {
            $this->activatedLocales[$localeIdentifiersKey] = ($this->findActivatedLocalesByIdentifiers)($localeIdentifiers);
        }

        return $this->activatedLocales[$localeIdentifiersKey];
    }
}
