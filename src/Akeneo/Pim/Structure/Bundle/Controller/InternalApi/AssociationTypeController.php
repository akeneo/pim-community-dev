<?php

namespace Akeneo\Pim\Structure\Bundle\Controller\InternalApi;

use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Association type controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeController
{
    /** @var AssociationTypeRepositoryInterface */
    protected $associationTypeRepo;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var RemoverInterface */
    protected $remover;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var SaverInterface */
    protected $saver;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var UserContext */
    protected $userContext;

    /** @var NormalizerInterface */
    protected $constraintViolationNormalizer;

    /**
     * @param AssociationTypeRepositoryInterface $associationTypeRepo
     * @param NormalizerInterface                $normalizer
     * @param RemoverInterface                   $remover
     * @param ObjectUpdaterInterface             $updater
     * @param SaverInterface                     $saver
     * @param ValidatorInterface                 $validator
     * @param UserContext                        $userContext
     * @param NormalizerInterface                $constraintViolationNormalizer
     */
    public function __construct(
        AssociationTypeRepositoryInterface $associationTypeRepo,
        NormalizerInterface $normalizer,
        RemoverInterface $remover,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver,
        ValidatorInterface $validator,
        UserContext $userContext,
        NormalizerInterface $constraintViolationNormalizer
    ) {
        $this->associationTypeRepo = $associationTypeRepo;
        $this->normalizer = $normalizer;
        $this->remover = $remover;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->validator = $validator;
        $this->userContext = $userContext;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
    }

    /**
     * @return JsonResponse
     */
    public function indexAction()
    {
        $associationTypes = $this->associationTypeRepo->findAll();

        $data = $this->normalizer->normalize($associationTypes, 'internal_api');

        return new JsonResponse($data);
    }

    /**
     * @param string $identifier
     *
     * @return JsonResponse
     */
    public function getAction($identifier)
    {
        $associationType = $this->getAssociationTypeOr404($identifier);

        return new JsonResponse(
            $this->normalizer->normalize($associationType, 'internal_api')
        );
    }

    /**
     * @param Request $request
     * @param string  $identifier
     *
     * @return Response
     *
     * @AclAncestor("pim_enrich_associationtype_edit")
     */
    public function postAction(Request $request, $identifier)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $associationType = $this->getAssociationTypeOr404($identifier);

        $data = json_decode($request->getContent(), true);
        $this->updater->update($associationType, $data);

        $violations = $this->validator->validate($associationType);

        if (0 < $violations->count()) {
            $errors = $this->normalizer->normalize(
                $violations,
                'internal_api'
            );

            return new JsonResponse($errors, 400);
        }

        $this->saver->save($associationType);

        return new JsonResponse(
            $this->normalizer->normalize(
                $associationType,
                'internal_api',
                $this->userContext->toArray()
            )
        );
    }

    /**
     * Remove action
     *
     * @param Request $request
     * @param string  $code
     *
     * @return Response
     *
     * @AclAncestor("pim_enrich_associationtype_remove")
     */
    public function removeAction(Request $request, $code)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $associationType = $this->getAssociationTypeOr404($code);

        $this->remover->remove($associationType);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Finds association type by code or throws not found exception
     *
     * @param string $code
     *
     * @throws NotFoundHttpException
     *
     * @return AssociationTypeInterface
     */
    protected function getAssociationTypeOr404(string $code)
    {
        $associationType = $this->associationTypeRepo->findOneByIdentifier($code);
        if (null === $associationType) {
            throw new NotFoundHttpException(
                sprintf('Association type with code "%s" not found', $code)
            );
        }

        return $associationType;
    }

    /**
     * Creates association type
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $associationType = new AssociationType();
        $this->updater->update($associationType, json_decode($request->getContent(), true));
        $violations = $this->validator->validate($associationType);

        $normalizedViolations = [];
        foreach ($violations as $violation) {
            $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                $violation,
                'internal_api',
                ['associationType' => $associationType]
            );
        }

        if (count($normalizedViolations) > 0) {
            return new JsonResponse(['values' => $normalizedViolations], 400);
        }

        $this->saver->save($associationType);

        return new JsonResponse($this->normalizer->normalize(
            $associationType,
            'internal_api'
        ));
    }
}
