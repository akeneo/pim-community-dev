<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Controller\InternalApi;

use Akeneo\Tool\Bundle\MeasureBundle\Application\ValidateUnit\ValidateUnitCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidateUnitAction
{
    private ValidatorInterface $validator;

    private NormalizerInterface $violationNormalizer;

    public function __construct(
        ValidatorInterface $validator,
        NormalizerInterface $violationNormalizer
    ) {
        $this->validator = $validator;
        $this->violationNormalizer = $violationNormalizer;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $measurementFamilyCode = $request->get('measurement_family_code');

        $decodedRequest = $this->decodeRequest($request);
        $validateUnitCommand = $this->createValidateUnitCommand($measurementFamilyCode, $decodedRequest);

        try {
            $violations = $this->validator->validate($validateUnitCommand);
            if ($violations->count() > 0) {
                return new JsonResponse($this->violationNormalizer->normalize($violations), Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        } catch (MeasurementFamilyNotFoundException $ex) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }

        return new Response(null, Response::HTTP_OK);
    }

    private function decodeRequest(Request $request): array
    {
        $decodedRequest = json_decode($request->getContent(), true);

        if (null === $decodedRequest) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $decodedRequest;
    }

    private function createValidateUnitCommand(
        string $measurementFamilyCode,
        array $decodedRequest
    ): ValidateUnitCommand {
        $command = new ValidateUnitCommand();
        $command->measurementFamilyCode = $measurementFamilyCode;
        $command->code = $decodedRequest['code'];
        $command->labels = $decodedRequest['labels'];
        $command->convert_from_standard = $decodedRequest['convert_from_standard'];
        $command->symbol = $decodedRequest['symbol'];

        return $command;
    }
}
