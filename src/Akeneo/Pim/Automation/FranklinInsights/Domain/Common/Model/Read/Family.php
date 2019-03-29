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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;

class Family
{
    /** @var FamilyCode */
    private $code;

    /** @var array|string[] */
    private $labels;

    /**
     * @param FamilyCode $code
     * @param string[]   $labels [locale => translated_label]
     */
    public function __construct(FamilyCode $code, array $labels)
    {
        $this->code = $code;
        $this->labels = $labels;
    }

    /**
     * @return FamilyCode
     */
    public function getCode(): FamilyCode
    {
        return $this->code;
    }

    /**
     * @return array|string[]
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getLabel(string $locale): string
    {
        return $this->labels[$locale] ?? sprintf('[%s]', $this->code);
    }
}
