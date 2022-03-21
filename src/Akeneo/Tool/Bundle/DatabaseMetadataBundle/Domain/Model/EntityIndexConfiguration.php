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
    public ?string $dateFieldName = null;
    public ?Closure $dataProcessing = null;
    public ?string  $filterFieldName = null;

    public function __construct(
        private array    $columnsName,
        private string   $tableName,
        private string   $identifierFieldName,
        private string   $sourceName
    ) {}

    public static function create(
        array   $columnsName,
        string  $tableName,
        string  $identifierFieldName,
        string  $sourceName,
    ): self{
        return new self($columnsName, $tableName, $identifierFieldName, $sourceName);
    }
    /**
     * @return array
     */
    public function getColumnsName(): array
    {
        return $this->columnsName;
    }


    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }


    /**
     * @return string
     */
    public function getIdentifierFieldName(): string
    {
        return $this->identifierFieldName;
    }

    /**
     * @return string
     */
    public function getSourceName(): string
    {
        return $this->sourceName;
    }

    /**
     * @return string
     */
    public function getDateFieldName(): ?string
    {
        return $this->dateFieldName;
    }

    /**
     * @param string $dateFieldName
     */
    public function setDateFieldName(?string $dateFieldName): EntityIndexConfiguration
    {
        $this->dateFieldName = $dateFieldName;
        return $this;
    }

    /**
     * @return Closure
     */
    public function getDataProcessing(): ?Closure
    {
        return $this->dataProcessing;
    }

    /**
     * @param Closure $dataProcessing
     */
    public function setDataProcessing(?Closure $dataProcessing): EntityIndexConfiguration
    {
        $this->dataProcessing = $dataProcessing;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilterFieldName(): ?string
    {
        return $this->filterFieldName;
    }

    /**
     * @param string $filterFieldName
     */
    public function setFilterFieldName(?string $filterFieldName): EntityIndexConfiguration
    {
        $this->filterFieldName = $filterFieldName;
        return $this;
    }
}