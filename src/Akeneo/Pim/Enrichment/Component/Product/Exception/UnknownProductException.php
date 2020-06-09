<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessageInterface;

/**
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UnknownProductException extends \Exception implements
    DomainErrorInterface,
    TemplatedErrorMessageInterface
{
    /** @var string */
    private $messageTemplate;

    /** @var array */
    private $messageParameters;

    public function __construct(string $productIdentifier)
    {
        $this->messageTemplate = 'The %s product does not exist in your PIM.';
        $this->messageParameters = [$productIdentifier];

        parent::__construct(sprintf($this->messageTemplate, ...$this->messageParameters));
    }

    public function getMessageTemplate(): string
    {
        return $this->messageTemplate;
    }

    public function getMessageParameters(): array
    {
        return $this->messageParameters;
    }
}
