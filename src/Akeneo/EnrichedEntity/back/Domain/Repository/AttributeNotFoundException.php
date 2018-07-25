<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Repository;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeNotFoundException extends \RuntimeException
{
    public static function withIdentifier(AttributeIdentifier $identifier): self
    {
        $message = sprintf(
            'Could not find attribute with enriched entity "%s" and identifier "%s"',
            $identifier->getEnrichedEntityIdentifier(),
            $identifier->getIdentifier()
        );

        return new self($message);
    }
}
