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

class AssociationTypeSource implements SourceInterface
{
    public const TYPE = 'association_type';

    private string $uuid;
    private string $code;
    private bool $isQuantified;
    private OperationCollection $operationCollection;
    private SelectionInterface $selection;

    public function __construct(
        string $uuid,
        string $code,
        bool $isQuantified,
        OperationCollection $operationCollection,
        SelectionInterface $selection
    ) {
        $this->uuid = $uuid;
        $this->code = $code;
        $this->isQuantified = $isQuantified;
        $this->operationCollection = $operationCollection;
        $this->selection = $selection;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function isQuantified(): bool
    {
        return $this->isQuantified;
    }

    public function getOperationCollection(): OperationCollection
    {
        return $this->operationCollection;
    }

    public function getSelection(): SelectionInterface
    {
        return $this->selection;
    }
}
