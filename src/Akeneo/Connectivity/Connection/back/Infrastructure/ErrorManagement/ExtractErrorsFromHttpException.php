<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement;

use Akeneo\Connectivity\Connection\Application\ErrorManagement\Service\ExtractErrorsFromHttpExceptionInterface;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\TechnicalError;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Serializer\Serializer;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExtractErrorsFromHttpException implements ExtractErrorsFromHttpExceptionInterface
{
    /** @var Serializer */
    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function extractAll(HttpException $httpException, ConnectionCode $connectionCode): array
    {
        if (
            false === $httpException instanceof UnprocessableEntityHttpException
            && false === $httpException instanceof NotFoundHttpException
        ) {
            return [];
        }

        $json = $this->serializer->serialize($httpException, 'json', new Context());

        if ($httpException instanceof ViolationHttpException) {
            return $this->extractViolationErrors($connectionCode, $json);
        }

        return [new TechnicalError($connectionCode, $json)];
    }

    /**
     * @return BusinessError[]
     */
    private function extractViolationErrors(ConnectionCode $connectionCode, string $json): array
    {
        $data = json_decode($json, true);

        $errors = [];
        foreach ($data['errors'] as $error) {
            $errors[] = new BusinessError($connectionCode, json_encode($error));
        }

        return $errors;
    }
}
