<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Category\Infrastructure\Converter\InternalApi;

use Akeneo\Category\Application\Converter\Checker\RequirementChecker;
use Akeneo\Category\Infrastructure\Converter\InternalApi\InternalApiToStd;
use Akeneo\Category\Infrastructure\Exception\ArrayConversionException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type InternalApi from InternalApiToStd
 * @phpstan-import-type StandardInternalApi from InternalApiToStd
 */
class InternalApiPermissionToStd extends InternalApiToStd
{
    public function __construct(
        private readonly RequirementChecker $checker,
    ) {
        parent::__construct($this->checker);
    }

    /**
     * @param InternalApi $data
     *
     * @return StandardInternalApi
     *
     * @throws ArrayConversionException
     */
    public function convert(array $data): array
    {
        $convertedData = parent::convert($data);

        $convertedData['permissions'] = $data['permissions'];

        return $convertedData;
    }
}
