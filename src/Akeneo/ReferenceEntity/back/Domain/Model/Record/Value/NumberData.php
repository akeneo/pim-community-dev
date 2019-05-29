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

namespace Akeneo\ReferenceEntity\Domain\Model\Record\Value;

use Webmozart\Assert\Assert;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class NumberData implements ValueDataInterface
{
    /** @var string */
    private $number;

    private function __construct(string $number)
    {
        Assert::stringNotEmpty($number, 'Number data should be a non empty string');

        $this->number = $number;
    }

    /**
     * @return string
     */
    public function normalize()
    {
        return $this->number;
    }

    public static function createFromNormalize($normalizedData): ValueDataInterface
    {
        Assert::string($normalizedData, 'Normalized data should be a string');

        return new self($normalizedData);
    }

    public static function fromString(string $string)
    {
        return new self($string);
    }
}
