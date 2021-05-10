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

namespace Akeneo\Pim\TableAttribute\Domain\Config;

use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
final class LabelCollection
{
    /** @var string[] */
    private array $labels;

    private function __construct(array $labels)
    {
        $this->labels = $labels;
    }

    public static function fromNormalized(array $normalizedLabels): self
    {
        Assert::allString($normalizedLabels);
        Assert::allStringNotEmpty(\array_keys($normalizedLabels));

        return new self($normalizedLabels);
    }

    /**
     * @return string[]
     */
    public function labels(): array
    {
        return $this->labels;
    }
}
