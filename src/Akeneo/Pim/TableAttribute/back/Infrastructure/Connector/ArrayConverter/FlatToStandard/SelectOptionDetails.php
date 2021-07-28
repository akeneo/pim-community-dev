<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;

final class SelectOptionDetails implements ArrayConverterInterface
{
    private FieldsRequirementChecker $fieldsRequirementChecker;

    public function __construct(FieldsRequirementChecker $fieldsRequirementChecker)
    {
        $this->fieldsRequirementChecker = $fieldsRequirementChecker;
    }

    public function convert(array $item, array $options = []): array
    {
        $this->fieldsRequirementChecker->checkFieldsPresence($item, ['code', 'attribute', 'column']);
        $this->fieldsRequirementChecker->checkFieldsFilling($item, ['code', 'attribute', 'column']);

        $labels = [];
        foreach ($item as $key => $value) {
            $matches = [];
            if (\preg_match('/^label-(?P<locale>\w+)$/', $key, $matches)) {
                $labels[$matches['locale']] = $value;
            }
        }

        $converted = [
            'attribute' => $item['attribute'],
            'column' => $item['column'],
            'code' => $item['code'],
        ];

        if (\count($labels) > 0) {
            $converted['labels'] = $labels;
        }

        return $converted;
    }
}
