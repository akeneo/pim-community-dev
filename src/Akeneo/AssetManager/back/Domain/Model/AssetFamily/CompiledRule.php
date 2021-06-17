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

namespace Akeneo\AssetManager\Domain\Model\AssetFamily;

/**
 * @author Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class CompiledRule
{
    private array $conditions;

    private array $actions;

    public function __construct(array $conditions, array $actions)
    {
        $this->conditions = $conditions;
        $this->actions = $actions;
    }

    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function getActions(): array
    {
        return $this->actions;
    }
}
