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

final class CustomAction
{
    public $type;
    public $data;

    public function __construct(array $data)
    {
        $this->type = $data['type'] ?? null;
        $this->data = $data;
    }
}
