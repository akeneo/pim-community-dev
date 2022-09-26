<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Command\GroupProductsCommand;
use Akeneo\Pim\Enrichment\Component\Product\Command\GroupProductsHandler;
use Akeneo\Pim\Enrichment\Component\Product\Factory\GroupFactory;
use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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
    private const MAX_PRODUCTS = 5;

    public function __construct(
        protected GroupRepositoryInterface $groupRepository,
        protected ProductRepositoryInterface $productRepository,
        protected NormalizerInterface $normalizer,
        protected UserContext $userContext,
        protected ObjectUpdaterInterface $updater,
        protected ValidatorInterface $validator,
        protected NormalizerInterface $violationNormalizer,
        protected SaverInterface $saver,
        protected RemoverInterface $remover,
        protected GroupFactory $groupFactory,
        protected NormalizerInterface $constraintViolationNormalizer,
        protected GroupProductsHandler $groupProductsHandler
    ) {
    }

    public function searchAction(Request $request): JsonResponse
    {
        $groups = $this->groupRepository->getOptions(
            $dataLocale = $this->userContext->getUiLocaleCode(),
            null,
            $request->get('search'),
            $this->parseOptions($request)
        );

        return new JsonResponse($groups);
    }

    public function getAction(string $identifier): JsonResponse
    {
        $group = $this->groupRepository->findOneBy(['code' => $identifier]);

        return new JsonResponse($this->normalizer->normalize($group, 'internal_api', $this->userContext->toArray()));
    }

    /**
     * Displays the products of a group
     *
     * @AclAncestor("pim_enrich_product_index")
     */
    public function listProductsAction(string $identifier): JsonResponse
    {
        $group = $this->groupRepository->findOneBy(['code' => $identifier]);

        if (!$group) {
            throw new NotFoundHttpException(sprintf('Group with code "%s" not found', $identifier));
        }

        return new JsonResponse($this->normalizer->normalize([
            'products'     => array_values($this->productRepository->getProductsByGroup($group, self::MAX_PRODUCTS)),
            'productCount' => $this->productRepository->getProductCountByGroup($group)
        ], 'internal_api', $this->userContext->toArray()));
    }

    /**
     * @throws NotFoundHttpException     If product is not found or the user cannot see it
     * @throws AccessDeniedHttpException If the user does not have permissions to edit the product
     *
     * @AclAncestor("pim_enrich_group_edit")
     */
    public function postAction(Request $request, string $code): Response
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

        if (array_key_exists('products', $data)) {
            $this->groupProductsHandler->handle(new GroupProductsCommand($group->getId(), $data['products']));
        }

        return new JsonResponse($this->normalizer->normalize(
            $group,
            'internal_api',
            $this->userContext->toArray()
        ));
    }

    /**
     * @AclAncestor("pim_enrich_group_remove")
     */
    public function removeAction(Request $request, string $code): Response
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

    /**
     * @AclAncestor("pim_enrich_group_create")
     */
    public function createAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $data = json_decode($request->getContent(), true);
        $group = $this->groupFactory->createGroup();
        $this->updater->update($group, $data);
        $violations = $this->validator->validate($group);

        $normalizedViolations = [];
        foreach ($violations as $violation) {
            $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                $violation,
                'internal_api',
                ['group' => $group]
            );
        }

        if (count($normalizedViolations) > 0) {
            return new JsonResponse(['values' => $normalizedViolations], 400);
        }

        $this->saver->save($group);

        return new JsonResponse($this->normalizer->normalize(
            $group,
            'internal_api'
        ));
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    private function parseOptions(Request $request): array
    {
        $options = $request->get('options', []);
        $options['type'] = 'code';

        if (!isset($options['limit'])) {
            $options['limit'] = SearchableRepositoryInterface::FETCH_LIMIT;
        }

        if (0 > intval($options['limit'])) {
            $options['limit'] = null;
        }

        if (!isset($options['locale'])) {
            $options['locale'] = null;
        }

        if (isset($options['identifiers'])) {
            $options['ids'] = explode(',', $options['identifiers']);
        }

        return $options;
    }
}
