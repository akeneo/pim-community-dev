<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessage;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessageInterface;

/**
 * @author    Valentin Dijkstra <valentin.dijkstra@akeneo.com>
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class LocalizableAttributeException extends \LogicException implements
    DomainErrorInterface,
    TemplatedErrorMessageInterface
{
    private TemplatedErrorMessage $templatedMessage;

    private function __construct(TemplatedErrorMessage $templatedMessage)
    {
        parent::__construct((string) $templatedMessage);
        $this->templatedMessage = $templatedMessage;
    }

    public static function withCode(string $attributeCode): self
    {
        return new self(
            new TemplatedErrorMessage(
                'The {attribute_code} attribute requires a locale.',
                ['attribute_code' => $attributeCode]
            )
        );
    }

    public function getTemplatedErrorMessage(): TemplatedErrorMessage
    {
        return $this->templatedMessage;
    }
}
