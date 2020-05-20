<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\ErrorManagement\Service;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\ApiErrorInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ExtractErrorsFromHttpExceptionInterface
{
    /**
     * @param HttpException $httpException
     *
     * @return ApiErrorInterface[]
     */
    public function extractAll(HttpException $httpException): array;
}
