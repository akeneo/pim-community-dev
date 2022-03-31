<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Model;

use Closure;

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EntityIndexConfiguration
{
    private ?string $dateFieldName = null;
    private ?Closure $dataProcessing = null;
    private ?string  $filterFieldName = null;

    private function __construct(
        private array    $columnsName,
        private string   $tableName,
        private string   $identifierFieldName,
        private string   $sourceName
    ) {
    }

    public static function create(
        array   $columnsName,
        string  $tableName,
        string  $identifierFieldName,
        string  $sourceName,
    ): self {
        return new self($columnsName, $tableName, $identifierFieldName, $sourceName);
    }

    public function getColumnsName(): array
    {
        return $this->columnsName;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getIdentifierFieldName(): string
    {
        return $this->identifierFieldName;
    }

    public function getSourceName(): string
    {
        return $this->sourceName;
    }

    public function getDateFieldName(): ?string
    {
        return $this->dateFieldName;
    }

    public function setDateFieldName(?string $dateFieldName): void
    {
        $this->dateFieldName = $dateFieldName;
    }

    public function getDataProcessing(): ?Closure
    {
        return $this->dataProcessing;
    }

    public function setDataProcessing(?Closure $dataProcessing): void
    {
        $this->dataProcessing = $dataProcessing;
    }

    public function getFilterFieldName(): ?string
    {
        return $this->filterFieldName;
    }

    public function setFilterFieldName(?string $filterFieldName): void
    {
        $this->filterFieldName = $filterFieldName;
    }
}
