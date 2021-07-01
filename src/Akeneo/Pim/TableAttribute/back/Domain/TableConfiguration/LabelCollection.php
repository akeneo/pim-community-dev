<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
final class LabelCollection
{
    /** @var array<string, string> */
    private array $labels;

    /**
     * @param array<string, string> $labels
     */
    private function __construct(array $labels)
    {
        $this->labels = $labels;
    }

    /**
     * @param array<string, string>|\stdClass $normalizedLabels
     */
    public static function fromNormalized($normalizedLabels): self
    {
        if ($normalizedLabels instanceof \stdClass) {
            $normalizedLabels = [];
        }
        Assert::isArray($normalizedLabels);
        Assert::allString($normalizedLabels);
        Assert::allStringNotEmpty(\array_keys($normalizedLabels));

        return new self($normalizedLabels);
    }

    /**
     * @return array<string, string>|\stdClass
     */
    public function normalize()
    {
        return 0 === count($this->labels) ? (object) [] : $this->labels;
    }
}
