<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

/**
 * @author    Valentin Dijkstra <valentin.dijkstra@akeneo.com>
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class LocalizableAttributeException extends \LogicException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function withCode(string $attributeCode): self
    {
        return new self(sprintf('The %s attribute requires a locale.', $attributeCode));
    }
}
