<?php

namespace Pim\Bundle\ApiBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\CatalogBundle\Version;
use Pim\Component\Api\Exception\DocumentedHttpException;
use Pim\Component\Api\Exception\ViolationHttpException;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyController
{
    /** @var FamilyRepositoryInterface */
    protected $repository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var  ValidatorInterface */
    protected $validator;

    /** @var SimpleFactoryInterface */
    protected $factory;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var SaverInterface */
    protected $saver;

    /** @var RouterInterface */
    protected $router;

    /** @var string */
    protected $documentationUrl;

    /**
     * @param FamilyRepositoryInterface $repository
     * @param NormalizerInterface       $normalizer
     * @param SimpleFactoryInterface    $factory
     * @param ObjectUpdaterInterface    $updater
     * @param ValidatorInterface        $validator
     * @param SaverInterface            $saver
     * @param RouterInterface           $router
     * @param string                    $documentationUrl
     */
    public function __construct(
        FamilyRepositoryInterface $repository,
        NormalizerInterface $normalizer,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        SaverInterface $saver,
        RouterInterface $router,
        $documentationUrl
    ) {
        $this->repository       = $repository;
        $this->normalizer       = $normalizer;
        $this->factory          = $factory;
        $this->updater          = $updater;
        $this->validator        = $validator;
        $this->saver            = $saver;
        $this->router           = $router;
        $this->documentationUrl = sprintf($documentationUrl, substr(Version::VERSION, 0, 3));
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     */
    public function getAction(Request $request, $code)
    {
        $family = $this->repository->findOneByIdentifier($code);
        if (null === $family) {
            throw new NotFoundHttpException(sprintf('Family "%s" does not exist.', $code));
        }

        $familyStandard = $this->normalizer->normalize($family, 'standard');

        return new JsonResponse($familyStandard);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        //@TODO limit will be set in configuration in an other PR
        $limit = $request->query->get('limit', 10);
        $page = $request->query->get('page', 1);

        //@TODO add parameterValidator to validate limit and page

        $offset = $limit * ($page - 1);

        $families = $this->repository->findBy([], [], $limit, $offset);

        $familiesStandard = $this->normalizer->normalize($families, 'external_api');

        //@TODO use paginate method before return results

        return new JsonResponse($familiesStandard);
    }

    /**
     * @param Request $request
     *
     * @throws BadRequestHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        $data = $this->getDecodedContent($request->getContent());

        $family = $this->factory->create();
        $this->updateFamily($family, $data);
        $this->validateFamily($family);

        $this->saver->save($family);

        $response = $this->getCreateResponse($family);

        return $response;
    }

    /**
     * @param Request $request
     *
     * @throws BadRequestHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return JsonResponse
     */
    public function partialUpdateAction(Request $request, $code)
    {
        $data = $this->getDecodedContent($request->getContent());

        $isCreation = false;
        $family = $this->repository->findOneByIdentifier($code);

        if (null === $family) {
            $isCreation = true;
            $this->validateCodeConsistency($code, $data);
            $data['code'] = $code;
            $family = $this->factory->create();
        }

        $this->updateFamily($family, $data);
        $this->validateFamily($family);

        $this->saver->save($family);

        $response = $isCreation ? $this->getCreateResponse($family) : $this->getUpdateResponse($family);

        return $response;
    }

    /**
     * Get the JSON decoded content. If the content is not a valid JSON, it throws an error 400.
     *
     * @param string $content content of a request to decode
     *
     * @throws BadRequestHttpException
     *
     * @return array
     */
    protected function getDecodedContent($content)
    {
        $decodedContent = json_decode($content, true);

        if (null === $decodedContent) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $decodedContent;
    }

    /**
     * Update a family. It throws an error 422 if a problem occurred during the update.
     *
     * @param FamilyInterface $family family to update
     * @param array           $data   data of the request already decoded, it should be the standard format
     *
     * @throws UnprocessableEntityHttpException
     */
    protected function updateFamily(FamilyInterface $family, array $data)
    {
        try {
            $this->updater->update($family, $data);
        } catch (UnknownPropertyException $exception) {
            throw new DocumentedHttpException(
                $this->documentationUrl,
                sprintf(
                    'Property "%s" does not exist. Check the standard format documentation.',
                    $exception->getPropertyName()
                ),
                $exception
            );
        } catch (PropertyException $exception) {
            throw new DocumentedHttpException(
                $this->documentationUrl,
                sprintf('%s Check the standard format documentation.', $exception->getMessage()),
                $exception
            );
        }
    }

    /**
     * Validate a family. It throws an error 422 with every violated constraints if
     * the validation failed.
     *
     * @param FamilyInterface $family
     *
     * @throws ViolationHttpException
     */
    protected function validateFamily(FamilyInterface $family)
    {
        $violations = $this->validator->validate($family);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }
    }

    /**
     * Get a response with HTTP code 201 when an object is created.
     *
     * @param FamilyInterface $family
     *
     * @return Response
     */
    protected function getCreateResponse(FamilyInterface $family)
    {
        $response = new Response(null, Response::HTTP_CREATED);
        $route = $this->router->generate('pim_api_rest_family_get', ['code' => $family->getCode()], true);
        $response->headers->set('Location', $route);

        return $response;
    }

    /**
     * Get a response with HTTP code 204 when an object is updated.
     *
     * @param FamilyInterface $family
     *
     * @return Response
     */
    protected function getUpdateResponse(FamilyInterface $family)
    {
        $response = new Response(null, Response::HTTP_NO_CONTENT);
        $route = $this->router->generate('pim_api_rest_family_get', ['code' => $family->getCode()], true);
        $response->headers->set('Location', $route);

        return $response;
    }

    /**
     * Throw an exception if the code provided in the url and the code provided in the request body
     * are not equals when creating a family with a PATCH method.
     *
     * The code in the request body is optional when we create a resource with PATCH.
     *
     * @param string $code code provided in the url
     * @param array  $data body of the request already decoded
     *
     * @throws UnprocessableEntityHttpException
     */
    protected function validateCodeConsistency($code, array $data)
    {
        if (isset($data['code']) && $code !== $data['code']) {
            throw new UnprocessableEntityHttpException(
                sprintf(
                    'The code "%s" provided in the request body must match the code "%s" provided in the url.',
                    $data['code'],
                    $code
                )
            );
        }
    }
}
