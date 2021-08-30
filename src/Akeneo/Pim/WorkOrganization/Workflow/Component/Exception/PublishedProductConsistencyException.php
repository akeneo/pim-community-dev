<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Exception;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Exception raises when try to remove an entity linked to published product
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class PublishedProductConsistencyException extends \Exception implements HttpExceptionInterface
{
    /**
     * @param string     $message
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct(
        $message,
        $code = 422,
        \Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->getCode();
    }

    public function getHeaders(): array
    {
        return [];
    }
}
