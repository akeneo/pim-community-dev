<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Query;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExternalApiSqlParameters
{
    /**
     * @param array<string, mixed>|null $params
     * @param array<string, int>|null $types
     */
    public function __construct(
        private readonly ?string $sqlWhere = null,
        private ?array $params = null,
        private ?array $types = null,
        private ?string $limitAndOffset = null,
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
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

    /**
     * @return array<string, int>|null
     */
    public function getTypes(): ?array
    {
        return $this->types;
    }

    /**
     * @param array<string, mixed>|null $params
     */
    public function setParams(?array $params): ExternalApiSqlParameters
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @param array<string, int>|null $types
     */
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
