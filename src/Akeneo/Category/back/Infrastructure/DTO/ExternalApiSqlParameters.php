<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\DTO;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExternalApiSqlParameters
{
    public function __construct(
        private ?string $sqlWhere = null,
        private ?array $params = null,
        private ?array $types = null,
        private ?string $limitAndOffset = null,
    ) {
    }

    public function getParams(): ?array
    {
        return $this->params;
    }

    public function getSqlWhere(): ?string
    {
        return $this->sqlWhere;
    }

    public function getLimitAndOffset(): ?string
    {
        return $this->limitAndOffset;
    }

    public function getTypes(): ?array
    {
        return $this->types;
    }

    public function setSqlWhere(?string $sqlWhere): ExternalApiSqlParameters
    {
        $this->sqlWhere = $sqlWhere;

        return $this;
    }

    public function setParams(?array $params): ExternalApiSqlParameters
    {
        $this->params = $params;

        return $this;
    }

    public function setTypes(?array $types): ExternalApiSqlParameters
    {
        $this->types = $types;

        return $this;
    }

    public function setLimitAndOffset(?string $limitAndOffset): ExternalApiSqlParameters
    {
        $this->limitAndOffset = $limitAndOffset;

        return $this;
    }
}
