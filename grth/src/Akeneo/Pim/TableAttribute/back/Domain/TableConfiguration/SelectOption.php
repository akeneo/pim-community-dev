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

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\SelectOptionCode;
use Webmozart\Assert\Assert;

final class SelectOption
{
    private SelectOptionCode $code;
    private LabelCollection $labels;

    private function __construct(SelectOptionCode $code, LabelCollection $labels)
    {
        $this->code = $code;
        $this->labels = $labels;
    }

    /**
     * @param array<string, mixed>|\stdClass $normalized
     */
    public static function fromNormalized($normalized): self
    {
        if ($normalized instanceof \stdClass) {
            $normalized = [];
        }
        Assert::isArray($normalized);
        Assert::keyExists($normalized, 'code');
        Assert::stringNotEmpty($normalized['code']);

        return new SelectOption(
            SelectOptionCode::fromString($normalized['code']),
            LabelCollection::fromNormalized($normalized['labels'] ?? [])
        );
    }

    public function code(): SelectOptionCode
    {
        return $this->code;
    }

    public function labels(): LabelCollection
    {
        return $this->labels;
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(): array
    {
        $labels = $this->labels->normalize();

        return [
            'code' => $this->code->asString(),
            'labels' => [] === $labels ? (object) [] : $labels,
        ];
    }
}
