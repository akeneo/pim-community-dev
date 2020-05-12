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

class SetAction implements ActionInterface
{
    public $field;
    public $value;
    public $scope;
    public $locale;

    public function __construct(array $data)
    {
        $this->field = $data['field'] ?? null;
        $this->value = $data['value'] ?? null;
        $this->scope = $data['scope'] ?? null;
        $this->locale = $data['locale'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'type' => 'set',
            'field' => $this->field,
            'value' => $this->value,
            'scope' => $this->scope,
            'locale' => $this->locale,
        ];
    }
}
