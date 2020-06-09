<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Error\Event;

use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DomainErrorEvent
{
    /** @var DomainErrorInterface */
    private $error;

    public function __construct(DomainErrorInterface $error)
    {
        $this->error = $error;
    }

    public function getError(): DomainErrorInterface
    {
        return $this->error;
    }
}
