<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Sql;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeInterface
{
    public function getCode(): string;

    public function getBackendType();

    public function getType();

    public function isRequired();

    public function isUnique();

    public function isLocalizable();

    public function isScopable();

    public function getAvailableLocaleCodes();

    public function isDecimalsAllowed();

    public function isLocaleSpecific(): bool;

    public function getMetricFamily();

    public function getDefaultMetricUnit();

    public function getReferenceDataName();

    public function getValidationRule();

    public function getValidationRegexp();

    public function getMaxFileSize();

    public function getAllowedExtensions(): array;

    public function getMaxCharacters();

    public function getNumberMin();

    public function getNumberMax();

    public function isNegativeAllowed();

    public function getDateMin();

    public function getDateMax();
}
