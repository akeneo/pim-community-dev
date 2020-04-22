<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExtractBusinessErrorsFromApiResponse
{
    /**
     * @param Response $response
     * @param ConnectionCode $connectionCode
     *
     * @return BusinessError[]
     */
    public static function extractAll(Response $response, ConnectionCode $connectionCode): array
    {
        $businessErrors = [];
        if (Response::HTTP_UNPROCESSABLE_ENTITY === $response->getStatusCode()) {
            $content = $response->getContent();
            $decodedContent = json_decode($content, true);
            if (isset($decodedContent['errors'])) {
                $businessErrors = [];
                foreach ($decodedContent['errors'] as $error) {
                    $businessErrors[] = new BusinessError($connectionCode, json_encode($error));
                }
            } else {
                $businessErrors[] = new BusinessError($connectionCode, $content);
            }
        }

        return $businessErrors;
    }
}
