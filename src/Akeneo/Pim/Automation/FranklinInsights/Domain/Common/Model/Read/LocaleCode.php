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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class LocaleCode
{
    private $localeCode;

    public function __construct(string $localeCode)
    {
        if (empty($localeCode)) {
            throw new \InvalidArgumentException(
                sprintf('Locale code "%s" is invalid', $localeCode)
            );
        }
        $this->localeCode = $localeCode;
    }

    public function __toString()
    {
        return $this->localeCode;
    }
}
