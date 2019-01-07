<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema;

use JsonSchema\Validator;

/**
 * Validate the structure of a records list (but not the records themselves).
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordListValidator
{
    public function validate(array $normalizedRecordList): array
    {
        $validator = new Validator();
        $normalizedRecordListObject = json_decode(json_encode($normalizedRecordList));

        $validator->validate($normalizedRecordListObject, $this->getJsonSchema());

        return $validator->getErrors();
    }

    private function getJsonSchema(): array
    {
        return [
            'type' => 'array',
            'items' => [
                'type' => 'object'
            ]
        ];
    }
}
