<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessage;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessageInterface;

/**
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UnknownProductException extends \Exception implements
    DomainErrorInterface,
    TemplatedErrorMessageInterface
{
    /** @var TemplatedErrorMessage */
    private $templatedErrorMessage;

    public function __construct(string $productIdentifier)
    {
        $this->templatedErrorMessage = new TemplatedErrorMessage(
            'The {product_identifier} product does not exist in your PIM or you do not have permission to access it.',
            ['product_identifier' => $productIdentifier]
        );

        parent::__construct((string) $this->templatedErrorMessage);
    }

    public function getTemplatedErrorMessage(): TemplatedErrorMessage
    {
        return $this->templatedErrorMessage;
    }
}
