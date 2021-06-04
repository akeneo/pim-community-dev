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

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;
use Akeneo\AssetManager\Domain\Query\Locale\FindActivatedLocalesByIdentifiersInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindActivatedLocalesByIdentifiers implements FindActivatedLocalesByIdentifiersInterface
{
    /** @var LocaleIdentifier[] */
    private array $activatedLocales = [];

    /**
     * {@inheritdoc}
     */
    public function find(LocaleIdentifierCollection $localeIdentifiers): LocaleIdentifierCollection
    {
        $activatedLocales = [];
        foreach ($localeIdentifiers as $localeIdentifier) {
            $localeCode = $localeIdentifier->normalize();
            if (in_array($localeCode, $this->activatedLocales)) {
                $activatedLocales[] = $localeCode;
            }
        }

        return LocaleIdentifierCollection::fromNormalized($activatedLocales);
    }

    public function save(LocaleIdentifier $localeIdentifier): void
    {
        $this->activatedLocales[] = $localeIdentifier->normalize();
    }
}
