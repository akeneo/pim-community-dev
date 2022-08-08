<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Converter\InternalAPI;

use Akeneo\Category\Application\Converter\ConverterInterface;
use Akeneo\Category\Application\Converter\FieldsRequirementChecker;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InternalAPIToStd implements ConverterInterface
{
    public function __construct(private FieldsRequirementChecker $fieldsRequirementChecker)
    {
    }

    public function convert(array $data): array
    {
        $this->fieldsRequirementChecker->checkFieldsExist(['code', 'labels'], $data['properties']);
        $this->fieldsRequirementChecker->checkFieldsNotEmpty(['code'], $data['properties']);

        $convertedData = [];
        $convertedData['code'] = $data['properties']['code'];
        $convertedData['labels'] = $data['properties']['labels'];

        return $convertedData;
    }
}
