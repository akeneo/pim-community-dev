<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement;

use Akeneo\Connectivity\Connection\Application\ErrorManagement\Service\ExtractErrorsFromHttpExceptionInterface;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\ApiErrorInterface;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\TechnicalError;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownAttributeException;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Serializer\Serializer;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
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

    /** @var Serializer */
    private $legacySerializer;

    public function __construct(Serializer $serializer, Serializer $legacySerializer)
    {
        $this->serializer = $serializer;
        $this->legacySerializer = $legacySerializer;
    }

    public function extractAll(HttpExceptionInterface $httpException): array
    {
        if (
            false === $httpException instanceof UnprocessableEntityHttpException
            && false === $httpException instanceof NotFoundHttpException
        ) {
            return [];
        }

        try {
            $json = $this->serializer->serialize($httpException, 'json', new Context());
        } catch (\Exception $e) {
            $json = $this->legacySerializer->serialize($httpException, 'json', new Context());
        }

        switch (true) {
            case $httpException instanceof ViolationHttpException:
                $extractedErrors = $this->extractViolationErrors($json);
                break;

            case $httpException->getPrevious() instanceof UnknownAttributeException:
                $extractedErrors = [new BusinessError($json)];
                break;

            default:
                $extractedErrors = [new TechnicalError($json)];
        }

        return $extractedErrors;
    }

    /**
     * @param string $json
     *
     * @return ApiErrorInterface[]
     */
    private function extractViolationErrors(string $json): array
    {
        $data = json_decode($json, true);

        $errors = [];
        foreach ($data['errors'] as $error) {
            $errors[] = new BusinessError(json_encode($error));
        }

        return $errors;
    }
}
