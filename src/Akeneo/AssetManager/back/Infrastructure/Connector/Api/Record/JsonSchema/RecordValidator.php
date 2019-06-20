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

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

/**
 * Validate a record using JSON Schema.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordValidator
{
    /** @var RecordPropertiesValidator */
    private $recordPropertiesValidator;

    /** @var RecordValuesValidator */
    private $recordValuesValidator;

    public function __construct(RecordPropertiesValidator $recordPropertiesValidator, RecordValuesValidator $recordValuesValidator)
    {
        $this->recordPropertiesValidator = $recordPropertiesValidator;
        $this->recordValuesValidator = $recordValuesValidator;
    }

    /**
     * Returns the list of errors formatted as:
     * [
     *      'property' => 'labels.fr_FR',
     *      'message'  => 'NULL value found, but a string is required'
     * ]
     *
     * Returns an empty array if there are no errors.
     */
    public function validate(ReferenceEntityIdentifier $referenceEntityIdentifier, array $normalizedRecord): array
    {
        $errors = $this->recordPropertiesValidator->validate($normalizedRecord);

        if (empty($errors) && !empty($normalizedRecord['values'])) {
            $errors = array_merge($errors, $this->recordValuesValidator->validate($referenceEntityIdentifier, $normalizedRecord));
        }

        return $errors;
    }
}
