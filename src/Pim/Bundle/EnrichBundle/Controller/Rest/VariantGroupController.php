<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * VariantGroup controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupController
{
    /** @var EntityRepository */
    protected $repository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var SaverInterface */
    protected $saver;

    /** @var RemoverInterface */
    protected $remover;

    /** @var UserContext */
    protected $userContext;

    /** @var AttributeConverterInterface */
    protected $attributeConverter;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var NormalizerInterface */
    protected $violationNormalizer;

    /** @var CollectionFilterInterface */
    protected $variantGroupDataFilter;

    /**
     * @param EntityRepository            $repository
     * @param NormalizerInterface         $normalizer
     * @param ObjectUpdaterInterface      $updater
     * @param SaverInterface              $saver
     * @param UserContext                 $userContext
     * @param AttributeConverterInterface $attributeConverter
     * @param ValidatorInterface          $validator
     * @param NormalizerInterface         $violationNormalizer
     * @param CollectionFilterInterface   $variantGroupDataFilter
     */
    public function __construct(
        GroupRepositoryInterface $repository,
        NormalizerInterface $normalizer,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver,
        RemoverInterface $remover,
        UserContext $userContext,
        AttributeConverterInterface $attributeConverter,
        ValidatorInterface $validator,
        NormalizerInterface $violationNormalizer,
        CollectionFilterInterface $variantGroupDataFilter
    ) {
        $this->repository = $repository;
        $this->normalizer = $normalizer;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->remover = $remover;
        $this->userContext = $userContext;
        $this->attributeConverter = $attributeConverter;
        $this->validator = $validator;
        $this->violationNormalizer = $violationNormalizer;
        $this->variantGroupDataFilter = $variantGroupDataFilter;
    }

    /**
     * Get the variant group collection
     *
     * @return JsonResponse
     */
    public function indexAction()
    {
        $variantGroups = $this->repository->getAllVariantGroups();

        $normalizedVariants = [];
        foreach ($variantGroups as $variantGroup) {
            $normalizedVariants[$variantGroup->getCode()] = $this->normalizer->normalize(
                $variantGroup,
                'internal_api',
                $this->userContext->toArray()
            );
        }

        return new JsonResponse($normalizedVariants);
    }

    /**
     * Get a single variant group
     *
     * @param string $code
     *
     * @return JsonResponse
     */
    public function getAction($code)
    {
        $variantGroup = $this->repository->findOneByIdentifier($code);
        if (null === $variantGroup) {
            throw new NotFoundHttpException(sprintf('Variant group with code "%s" not found', $code));
        }

        return new JsonResponse(
            $this->normalizer->normalize(
                $variantGroup,
                'internal_api',
                $this->userContext->toArray() + ['with_variant_group_values' => true]
            )
        );
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @throws NotFoundHttpException     If product is not found or the user cannot see it
     * @throws AccessDeniedHttpException If the user does not have right to edit the product
     *
     * @return Response
     */
    public function postAction(Request $request, $code)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $variantGroup = $this->repository->findOneByIdentifier($code);
        if (null === $variantGroup) {
            throw new NotFoundHttpException(sprintf('Variant group with code "%s" not found', $code));
        }

        $data = json_decode($request->getContent(), true);
        $data = $this->convertLocalizedAttributes($data);
        $data = $this->variantGroupDataFilter->filterCollection($data, null);

        $this->updater->update($variantGroup, $data);

        $violations = $this->validator->validate($variantGroup);
        $violations->addAll($this->validator->validate($variantGroup->getProductTemplate()));
        $violations->addAll($this->attributeConverter->getViolations());

        if (0 < $violations->count()) {
            $errors = $this->violationNormalizer->normalize(
                $violations,
                'internal_api',
                $this->userContext->toArray() + ['product' => $variantGroup->getProductTemplate()]
            );

            return new JsonResponse($errors, 400);
        }

        $this->saver->save($variantGroup, [
            'copy_values_to_products' => true
        ]);

        return new JsonResponse($this->normalizer->normalize(
            $variantGroup,
            'internal_api',
            $this->userContext->toArray() + ['with_variant_group_values' => true]
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

        $variantGroup = $this->repository->findOneByIdentifier($code);
        if (null === $variantGroup) {
            throw new NotFoundHttpException(sprintf('Variant group with code "%s" not found', $code));
        }

        $this->remover->remove($variantGroup);

        return new JsonResponse();
    }

    /**
     * Convert localized attributes to the default format
     *
     * @param array $data
     *
     * @return array
     */
    protected function convertLocalizedAttributes(array $data)
    {
        $locale = $this->userContext->getUiLocale()->getCode();
        $data['values'] = $this->attributeConverter->convertToDefaultFormats(
            $data['values'],
            ['locale' => $locale]
        );

        return $data;
    }
}
