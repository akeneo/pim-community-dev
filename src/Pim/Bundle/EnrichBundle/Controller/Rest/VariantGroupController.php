<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Localization\Localizer\LocalizedAttributeConverterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
    protected $variantGroupRepo;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ObjectUpdaterInterface */
    protected $variantGroupUpdater;

    /** @var SaverInterface */
    protected $variantGroupSaver;

    /** @var UserContext */
    protected $userContext;

    /** @var LocalizedAttributeConverterInterface */
    protected $localizedConverter;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param EntityRepository                     $variantGroupRepo
     * @param NormalizerInterface                  $normalizer
     * @param ObjectUpdaterInterface               $variantGroupUpdater
     * @param SaverInterface                       $variantGroupSaver
     * @param UserContext                          $userContext
     * @param LocalizedAttributeConverterInterface $localizedConverter
     * @param ValidatorInterface                   $validator
     */
    public function __construct(
        EntityRepository $variantGroupRepo,
        NormalizerInterface $normalizer,
        ObjectUpdaterInterface $variantGroupUpdater,
        SaverInterface $variantGroupSaver,
        UserContext $userContext,
        LocalizedAttributeConverterInterface $localizedConverter,
        ValidatorInterface $validator
    ) {
        $this->variantGroupRepo    = $variantGroupRepo;
        $this->normalizer          = $normalizer;
        $this->variantGroupUpdater = $variantGroupUpdater;
        $this->variantGroupSaver   = $variantGroupSaver;
        $this->userContext         = $userContext;
        $this->localizedConverter  = $localizedConverter;
        $this->validator           = $validator;
    }

    /**
     * Get the variant group collection
     *
     * @return JsonResponse
     */
    public function indexAction()
    {
        $variantGroups = $this->variantGroupRepo->getAllVariantGroups();

        $normalizedVariants = [];
        foreach ($variantGroups as $variantGroup) {
            $normalizedVariants[$variantGroup->getCode()] = $this->normalizer->normalize(
                $variantGroup,
                'internal_api',
                ['with_variant_group_values' => true]
            );
        }

        return new JsonResponse($normalizedVariants);
    }

    /**
     * Get a single variant group
     *
     * @param int $identifier
     *
     * @return JsonResponse
     */
    public function getAction($identifier)
    {
        if (is_numeric($identifier)) {
            $variantGroup = $this->variantGroupRepo->findOneBy(['id' => (int) $identifier]);
        } else {
            $variantGroup = $this->variantGroupRepo->findOneByIdentifier($identifier);
        }

        if (!$variantGroup) {
            throw new NotFoundHttpException(sprintf('Variant group with code "%s" not found', $identifier));
        }

        return new JsonResponse(
            $this->normalizer->normalize($variantGroup, 'internal_api', ['with_variant_group_values' => true])
        );
    }

    /**
     * @param Request $request
     * @param string  $id
     *
     * @throws NotFoundHttpException     If product is not found or the user cannot see it
     * @throws AccessDeniedHttpException If the user does not have right to edit the product
     *
     * @return JsonResponse
     */
    public function postAction(Request $request, $id)
    {
        $variantGroup = $this->variantGroupRepo->findOneBy(['id' => (int) $id]);
        if (!$variantGroup) {
            throw new NotFoundHttpException(sprintf('Variant group with id "%s" not found', $id));
        }

        // if ($this->objectFilter->filterObject($variantGroup, 'pim.internal_api.product.edit')) {
        //     throw new AccessDeniedHttpException();
        // }

        $data = json_decode($request->getContent(), true);
        // try {
        //     $data = $this->productEditDataFilter->filterCollection($data, null, ['product' => $product]);
        // } catch (ObjectNotFoundException $e) {
        //     throw new BadRequestHttpException();
        // }

        $data = $this->convertLocalizedAttributes($data);

        $this->variantGroupUpdater->update($variantGroup, $data);

        $violations = $this->validator->validate($variantGroup);
        $violations->addAll($this->validator->validate($variantGroup->getProductTemplate()));
        $violations->addAll($this->localizedConverter->getViolations());

        if (0 === $violations->count()) {
            $this->variantGroupSaver->save($variantGroup, ['flush' => true]);

            // $normalizationContext = $this->userContext->toArray() + [
            //     'filter_type'                => 'pim.internal_api.product_value.view',
            //     'disable_grouping_separator' => true
            // ];

            return new JsonResponse($this->normalizer->normalize(
                $variantGroup,
                'internal_api',
                ['with_variant_group_values' => true]
            ));
        } else {
            $errors = [
                'values' => $this->normalizer->normalize(
                    $violations,
                    'internal_api',
                    ['product' => $variantGroup->getProductTemplate()]
                )
            ];

            return new JsonResponse($errors, 400);
        }
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
        $locale         = $this->userContext->getUiLocale()->getCode();
        $data['values'] = $this->localizedConverter
            ->convertLocalizedToDefaultValues($data['values'], ['locale' => $locale]);

        return $data;
    }
}
