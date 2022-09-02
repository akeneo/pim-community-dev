<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Query\Attribute;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Platform\TailoredImport\Domain\Query\Attribute\GetIdentifierAttributeCodeInterface;

class GetIdentifierAttributeCode implements GetIdentifierAttributeCodeInterface
{
    private const IDENTIFIER_ATTRIBUTE_TYPE = 'pim_catalog_identifier';
    private ?string $identifierAttributeCode = null;

    public function __construct(
        private GetAttributes $getAttributes,
    ) {
    }

    public function execute(): string
    {
        if (null === $this->identifierAttributeCode) {
            $identifierAttribute = current($this->getAttributes->forType(self::IDENTIFIER_ATTRIBUTE_TYPE));

            if (false === $identifierAttribute) {
                throw new \RuntimeException('No identifier attribute found');
            }

            $this->identifierAttributeCode = $identifierAttribute->code();
        }

        return $this->identifierAttributeCode;
    }
}
