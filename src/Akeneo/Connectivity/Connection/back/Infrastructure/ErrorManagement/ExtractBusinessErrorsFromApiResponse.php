<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
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
     * @param string $connectionCode
     *
     * @return BusinessError[]
     */
    public function extractAll(Response $response, string $connectionCode): array
    {
        $businessErrors = [];
        switch (true) {
            case Response::HTTP_UNPROCESSABLE_ENTITY === $response->getStatusCode():
                $businessErrors = $this->extractFrom422($response, $connectionCode);
                break;
        }

        return $businessErrors;
    }

    /**
     * @param Response $response
     * @param string $connectionCode
     *
     * @return BusinessError[]
     */
    private function extractFrom422(Response $response, string $connectionCode): array
    {
        $content = $response->getContent();
        $decodedContent = json_decode($content, true);
        if (isset($decodedContent['errors'])) {
            /*
             * TODO Validations errors API-1068
             * $businessErrors = [];
             * foreach ($decodedContent['errors'] as $error) {
             *     $businessErrors[] = new BusinessError($connectionCode, json_encode($error));
             * }
             *
             * return $businessErrors;
             */

            return [];
        }

        return [new BusinessError($connectionCode, $content)];
    }
}
