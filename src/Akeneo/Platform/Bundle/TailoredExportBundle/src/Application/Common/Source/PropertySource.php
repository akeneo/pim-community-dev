<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Application\Common\Source;

use Akeneo\Platform\TailoredExport\Application\Common\Operation\OperationCollection;
use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;

class PropertySource implements SourceInterface
{
    public const TYPE = 'property';

    private string $uuid;
    private string $name;
    private OperationCollection $operations;
    private SelectionInterface $selection;

    public function __construct(
        string $uuid,
        string $name,
        OperationCollection $operations,
        SelectionInterface $selection
    ) {
        $this->uuid = $uuid;
        $this->name = $name;
        $this->operations = $operations;
        $this->selection = $selection;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOperationCollection(): OperationCollection
    {
        return $this->operations;
    }

    public function getSelection(): SelectionInterface
    {
        return $this->selection;
    }
}
