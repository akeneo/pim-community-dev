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

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Locale\FindActivatedLocalesByIdentifiersInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindActivatedLocalesByIdentifiers implements FindActivatedLocalesByIdentifiersInterface
{
    /** @var LocaleIdentifier[] */
    private $activatedLocales = [];

    /**
     * {@inheritdoc}
     */
    public function __invoke(array $localeIdentifiers): array
    {
        Assert::allIsInstanceOf($localeIdentifiers, LocaleIdentifier::class);

        $activatedLocales = [];
        foreach ($localeIdentifiers as $localeIdentifier) {
            $localeCode = $localeIdentifier->normalize();
            if (in_array($localeCode, $this->activatedLocales)) {
                $activatedLocales[] = $localeCode;
            }
        }

        return $activatedLocales;
    }

    public function save(LocaleIdentifier $localeIdentifier): void
    {
        $this->activatedLocales[] = $localeIdentifier->normalize();
    }
}
