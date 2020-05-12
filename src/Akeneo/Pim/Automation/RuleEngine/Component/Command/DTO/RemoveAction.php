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

final class RemoveAction implements ActionInterface
{
    public $field;
    public $items;
    public $scope;
    public $locale;
    public $includeChildren;

    public function __construct(array $data)
    {
        $this->field = $data['field'] ?? null;
        $this->items = $data['items'] ?? null;
        $this->scope = $data['scope'] ?? null;
        $this->locale = $data['locale'] ?? null;
        $this->includeChildren = $data['include_children'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'type' => 'remove',
            'field' => $this->field,
            'items' => $this->items,
            'scope' => $this->scope,
            'locale' => $this->locale,
            'include_children' => $this->includeChildren,
        ];
    }
}
