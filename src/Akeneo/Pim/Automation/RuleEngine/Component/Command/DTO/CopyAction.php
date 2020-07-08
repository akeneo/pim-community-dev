<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO;

use Webmozart\Assert\Assert;

final class CopyAction implements ActionInterface
{
    public $fromField;
    public $fromLocale;
    public $fromScope;

    public $toField;
    public $toLocale;
    public $toScope;

    public function __construct(array $data)
    {
        $this->fromField = $data['from_field'] ?? null;
        $this->fromLocale =$data['from_locale'] ?? null;
        $this->fromScope = $data['from_scope'] ?? null;

        $this->toField =   $data['to_field'] ?? null;
        $this->toLocale =  $data['to_locale'] ?? null;
        $this->toScope =   $data['to_scope'] ?? null;
    }

    public function toArray(): array
    {
        Assert::stringNotEmpty($this->fromField);
        Assert::nullOrStringNotEmpty($this->fromScope);
        Assert::nullOrStringNotEmpty($this->fromLocale);
        Assert::stringNotEmpty($this->toField);
        Assert::nullOrStringNotEmpty($this->toScope);
        Assert::nullOrStringNotEmpty($this->toLocale);

        return array_filter([
            'type' => 'copy',
            'from_field' => $this->fromField,
            'from_scope' => $this->fromScope,
            'from_locale' => $this->fromLocale,
            'to_field' => $this->toField,
            'to_scope' => $this->toScope,
            'to_locale' => $this->toLocale,
        ], function ($value): bool {
            return null !== $value;
        });
    }
}
