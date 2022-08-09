<?php
declare(strict_types=1);

namespace Akeneo\Category\Api\Model;

/**
 * A AttributeValues represents the attributes values of a category.
 *
 * @phpstan-type Locale string
 *
 * underscore-separated compound of attribute code, attribute UUID and possibly locale code
 * @phpstan-type AttributeKey string
 *
 * @phpstan-type TextAttributeValue string
 * @phpstan-type ImageAttributeValue array{"size":int, "file_path": string, "mime_type": string, "extension": string, "original_filename": string}
 * @phpstan-type AttributeValue TextAttributeValue | ImageAttributeValue

 * @phpstan-type AttributeValueWrap array{"locale"?: Locale|null, "data": AttributeValue}
 *
 * @phpstan-type AttributeValuesMap array<AttributeKey, AttributeValueWrap>
 *
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeValues
{

    /**
     * @param AttributeValuesMap $values
     */
    public function __construct(private array $values) {

    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(): array {
        return $this->values;
    }

}
