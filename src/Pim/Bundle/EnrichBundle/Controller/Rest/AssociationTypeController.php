<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    /**
     * @param AssociationTypeRepositoryInterface $associationTypeRepo
     * @param NormalizerInterface                $normalizer
     * @param RemoverInterface                   $remover
     * @param ObjectUpdaterInterface             $updater
     * @param SaverInterface                     $saver
     * @param ValidatorInterface                 $validator
     * @param UserContext                        $userContext
     */
    public function __construct(
        AssociationTypeRepositoryInterface $associationTypeRepo,
        NormalizerInterface $normalizer,
        RemoverInterface $remover,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver,
        ValidatorInterface $validator,
        UserContext $userContext
    ) {
        $this->associationTypeRepo = $associationTypeRepo;
        $this->normalizer = $normalizer;
        $this->remover = $remover;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->validator = $validator;
        $this->userContext = $userContext;
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
     *
     * @AclAncestor("pim_enrich_associationtype_edit")
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
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_enrich_associationtype_edit")
     */
    public function postAction(Request $request, $code)
    {
        $associationType = $this->getAssociationTypeOr404($code);

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
     * @param $code
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_enrich_associationtype_remove")
     */
    public function removeAction($code)
    {
        $associationType = $this->getAssociationTypeOr404($code);

        $this->remover->remove($associationType);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Finds association type by code or throws not found exception
     *
     * @param $code
     *
     * @throws NotFoundHttpException
     *
     * @return AssociationTypeInterface
     */
    private function getAssociationTypeOr404($code)
    {
        $associationType = $this->associationTypeRepo->findOneBy(
            ['code' => $code]
        );
        if (null === $associationType) {
            throw new NotFoundHttpException(
                sprintf('Association type with code "%s" not found', $code)
            );
        }

        return $associationType;
    }
}
