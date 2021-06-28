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

final class SelectOption
{
    private string $code;
    private LabelCollection $labels;

    private function __construct(string $code, LabelCollection $labels)
    {
        $this->code = $code;
        $this->labels = $labels;
    }

    /**
     * @param array<string, mixed> $normalized
     */
    public static function fromNormalized(array $normalized): self
    {
        Assert::keyExists($normalized, 'code');
        Assert::stringNotEmpty($normalized['code']);

        return new SelectOption($normalized['code'], LabelCollection::fromNormalized($normalized['labels'] ?? []));
    }

    public function code(): string
    {
        return $this->code;
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(): array
    {
        $labels = $this->labels->labels();

        return [
            'code' => $this->code,
            'labels' => [] === $labels ? (object) [] : $labels,
        ];
    }
}
