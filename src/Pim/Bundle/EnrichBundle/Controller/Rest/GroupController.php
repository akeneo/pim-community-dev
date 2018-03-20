<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Group controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupController
{
    /** @staticvar integer The maximum number of group products to be displayed */
    const MAX_PRODUCTS = 5;

    /** @var GroupRepositoryInterface */
    protected $groupRepository;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var NormalizerInterface */
    protected $violationNormalizer;

    /** @var SaverInterface */
    protected $saver;

    /** @var UserContext */
    protected $userContext;

    /** @var RemoverInterface */
    protected $remover;

    /**
     * @param GroupRepositoryInterface   $groupRepository
     * @param ProductRepositoryInterface $productRepository
     * @param NormalizerInterface        $normalizer
     * @param UserContext                $userContext
     * @param ObjectUpdaterInterface     $updater
     * @param ValidatorInterface         $validator
     * @param NormalizerInterface        $violationNormalizer
     * @param SaverInterface             $saver
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        ProductRepositoryInterface $productRepository,
        NormalizerInterface $normalizer,
        UserContext $userContext,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        NormalizerInterface $violationNormalizer,
        SaverInterface $saver,
        RemoverInterface $remover
    ) {
        $this->groupRepository = $groupRepository;
        $this->productRepository = $productRepository;
        $this->normalizer = $normalizer;
        $this->violationNormalizer = $violationNormalizer;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->userContext = $userContext;
        $this->validator = $validator;
        $this->remover = $remover;
    }

    /**
     * @return JsonResponse
     */
    public function indexAction()
    {
        $groups = $this->groupRepository->getAllGroupsExceptVariant();

        return new JsonResponse($this->normalizer->normalize($groups, 'internal_api'));
    }

    /**
     * @param string $identifier
     *
     * @return JsonResponse
     */
    public function getAction($identifier)
    {
        $group = $this->groupRepository->findOneBy(['code' => $identifier]);

        return new JsonResponse($this->normalizer->normalize($group, 'internal_api'));
    }

    /**
     * Display the products of a group
     *
     * @param string $identifier
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_enrich_product_index")
     */
    public function listProductsAction($identifier)
    {
        $group = $this->groupRepository->findOneBy(['code' => $identifier]);

        if (!$group) {
            throw new NotFoundHttpException(sprintf('Group with code "%s" not found', $identifier));
        }

        return new JsonResponse($this->normalizer->normalize([
            'products'     => array_values($this->productRepository->getProductsByGroup($group, self::MAX_PRODUCTS)),
            'productCount' => $this->productRepository->getProductCountByGroup($group)
        ], 'internal_api'));
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @throws NotFoundHttpException     If product is not found or the user cannot see it
     * @throws AccessDeniedHttpException If the user does not have permissions to edit the product
     *
     * @return Response
     */
    public function postAction(Request $request, $code)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $group = $this->groupRepository->findOneByIdentifier($code);
        if (null === $group) {
            throw new NotFoundHttpException(sprintf('Group with code "%s" not found', $code));
        }

        $data = json_decode($request->getContent(), true);
        $this->updater->update($group, $data);

        $violations = $this->validator->validate($group);

        if (0 < $violations->count()) {
            $errors = $this->violationNormalizer->normalize(
                $violations,
                'internal_api',
                $this->userContext->toArray()
            );

            return new JsonResponse($errors, 400);
        }

        $this->saver->save($group);

        return new JsonResponse($this->normalizer->normalize(
            $group,
            'internal_api',
            $this->userContext->toArray()
        ));
    }

    /**
     * Remove a variant group
     *
     * @param string $code
     *
     * @AclAncestor("pim_enrich_group_remove")
     *
     * @return Response
     */
    public function removeAction(Request $request, $code)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $group = $this->groupRepository->findOneByIdentifier($code);
        if (null === $group) {
            throw new NotFoundHttpException(sprintf('Group with code "%s" not found', $code));
        }

        $this->remover->remove($group);

        return new JsonResponse();
    }
}
