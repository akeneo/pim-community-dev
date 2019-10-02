<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Component\Localization\Localizer\LocalizerInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Query\AttributeIsAFamilyVariantAxis;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Factory\AttributeFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Attribute rest controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeController
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var ObjectFilterInterface */
    protected $attributeFilter;

    /** @var SearchableRepositoryInterface */
    protected $attributeSearchRepository;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var SaverInterface */
    protected $saver;

    /** @var RemoverInterface */
    protected $remover;

    /** @var AttributeFactory */
    protected $factory;

    /** @var UserContext */
    private $userContext;

    /** @var LocalizerInterface */
    protected $numberLocalizer;

    /** @var NormalizerInterface */
    private $lightAttributeNormalizer;

    /** @var TranslatorInterface */
    private $translator;

    /** @var AttributeIsAFamilyVariantAxis */
    private $attributeIsAFamilyVariantAxisQuery;

    /** @var ObjectRepository */
    private $channelRepository;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        NormalizerInterface $normalizer,
        TokenStorageInterface $tokenStorage,
        ObjectFilterInterface $attributeFilter,
        SearchableRepositoryInterface $attributeSearchRepository,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        SaverInterface $saver,
        RemoverInterface $remover,
        AttributeFactory $factory,
        UserContext $userContext,
        LocalizerInterface $numberLocalizer,
        NormalizerInterface $lightAttributeNormalizer,
        TranslatorInterface $translator,
        AttributeIsAFamilyVariantAxis $attributeIsAFamilyVariantAxisQuery,
        ObjectRepository $channelRepository = null // TODO Merge remove null on master
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->normalizer = $normalizer;
        $this->tokenStorage = $tokenStorage;
        $this->attributeFilter = $attributeFilter;
        $this->attributeSearchRepository = $attributeSearchRepository;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->saver = $saver;
        $this->remover = $remover;
        $this->factory = $factory;
        $this->userContext = $userContext;
        $this->numberLocalizer = $numberLocalizer;
        $this->lightAttributeNormalizer = $lightAttributeNormalizer;
        $this->translator = $translator;
        $this->attributeIsAFamilyVariantAxisQuery = $attributeIsAFamilyVariantAxisQuery;
        $this->channelRepository = $channelRepository;
    }

    /**
     * Get the attribute collection.
     *
     * TODO This action is only accessible via a GET or POST query, because of too long query URI. To respect standards,
     * a refactor must be done.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function indexAction(Request $request)
    {
        $options = $request->request->get(
            'options',
            ['limit' => SearchableRepositoryInterface::FETCH_LIMIT, 'locale' => null]
        );

        if ($request->request->has('identifiers')) {
            $options['identifiers'] = array_unique(explode(',', $request->request->get('identifiers')));
            $options['limit'] = count($options['identifiers']);
        }

        if ($request->request->has('types')) {
            $options['types'] = explode(',', $request->request->get('types'));
        }

        if ($request->request->has('attribute_groups')) {
            $options['attribute_groups'] = explode(',', $request->request->get('attribute_groups'));
        }

        if ($request->request->has('rights')) {
            $options['rights'] = (bool) $request->request->get('rights');
        }

        $token = $this->tokenStorage->getToken();
        $options['user_groups_ids'] = $token->getUser()->getGroupsIds();

        $attributes = $this->attributeSearchRepository->findBySearch(
            $request->request->get('search'),
            $options
        );

        $normalizedAttributes = array_map(function ($attribute) {
            return $this->lightAttributeNormalizer->normalize(
                $attribute,
                'internal_api',
                ['locale' => $this->userContext->getUiLocale()->getCode()]
            );
        }, $attributes);

        return new JsonResponse($normalizedAttributes);
    }

    /**
     * Get attribute by identifier
     *
     * @param Request $request
     * @param string  $identifier
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     */
    public function getAction(Request $request, $identifier)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($identifier);

        if ($request->query->getBoolean('apply_filters', true)) {
            $attribute = $this->attributeFilter
                ->filterObject($attribute, 'pim.internal_api.attribute.view') ? null : $attribute;
        }

        if (null === $attribute) {
            throw new NotFoundHttpException(sprintf('Attribute with code "%s" not found', $identifier));
        }

        return new JsonResponse($this->normalizer->normalize(
            $attribute,
            'internal_api',
            ['locale' => $this->userContext->getUiLocale()->getCode()]
        ));
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @AclAncestor("pim_enrich_attribute_create")
     */
    public function createAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $attribute = $this->factory->create();

        $data = json_decode($request->getContent(), true);

        $localizedDataViolations = $this->validateLocalizedData($data);
        $this->updateAttribute($attribute, $data);

        $violations = $this->validator->validate($attribute);
        $violations->addAll($localizedDataViolations);

        if (0 < $violations->count()) {
            $errors = $this->normalizer->normalize(
                $violations,
                'internal_api'
            );

            return new JsonResponse($errors, 400);
        }

        $this->saver->save($attribute);

        return new JsonResponse(
            $this->normalizer->normalize(
                $attribute,
                'internal_api',
                ['locale' => $this->userContext->getUiLocale()->getCode()]
            )
        );
    }

    /**
     * @param Request $request
     * @param string  $identifier
     *
     * @return Response
     *
     * @AclAncestor("pim_enrich_attribute_edit")
     */
    public function postAction(Request $request, $identifier)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $attribute = $this->getAttributeOr404($identifier);

        $data = json_decode($request->getContent(), true);

        $localizedDataViolations = $this->validateLocalizedData($data);
        $this->updateAttribute($attribute, $data);

        $violations = $this->validator->validate($attribute);
        $violations->addAll($localizedDataViolations);

        if (0 < $violations->count()) {
            $errors = $this->normalizer->normalize(
                $violations,
                'internal_api'
            );

            return new JsonResponse($errors, 400);
        }

        $this->saver->save($attribute);

        return new JsonResponse(
            $this->normalizer->normalize(
                $attribute,
                'internal_api',
                ['locale' => $this->userContext->getUiLocale()->getCode()]
            )
        );
    }

    /**
     * @param $code
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_enrich_attribute_remove")
     */
    public function removeAction(Request $request, string $code): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    'message' => 'An error occurred.',
                    'global' => true,
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $isAnFamilyVariantAxis = $this->attributeIsAFamilyVariantAxisQuery->execute($code);

        if ($isAnFamilyVariantAxis) {
            $message = $this->translator->trans('pim_enrich.family.info.cant_remove_attribute_used_as_axis');

            return new JsonResponse(
                [
                    'message' => $message,
                    'global' => true,
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $attribute = $this->getAttributeOr404($code);

        if (AttributeTypes::IDENTIFIER === $attribute->getType()) {
            $message = $this->translator->trans('flash.attribute.identifier_not_removable');

            return new JsonResponse(
                [
                    'message' => $message,
                    'global' => true,
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $channelCodes = $this->channelCodesUsedAsConversionUnit($code);
        if (count($channelCodes) > 0) {
            $message = $this->translator->trans('flash.attribute.used_as_conversion_unit', [
                '%channelCodes%' => join(', ', $channelCodes)
            ]);

            return new JsonResponse(
                [
                    'message' => $message,
                    'global' => true,
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->remover->remove($attribute);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param $identifier
     *
     * @throws NotFoundHttpException
     *
     * @return AttributeInterface
     */
    protected function getAttributeOr404($identifier)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($identifier);
        if (null === $attribute) {
            throw new NotFoundHttpException(
                sprintf('Attribute with identifier "%s" not found', $identifier)
            );
        }

        return $attribute;
    }

    /**
     * @param $data
     *
     * @return ConstraintViolationList
     */
    protected function validateLocalizedData($data)
    {
        $allViolations = new ConstraintViolationList();

        if (isset($data['number_min'])) {
            $violations = $this->numberLocalizer->validate($data['number_min'], 'number_min', [
                'locale' => $this->userContext->getUiLocale()->getCode()
            ]);

            if (null !== $violations && $violations->count() > 0) {
                $allViolations->addAll($violations);
            }
        }

        if (isset($data['number_max'])) {
            $violations = $this->numberLocalizer->validate($data['number_max'], 'number_max', [
                'locale' => $this->userContext->getUiLocale()->getCode()
            ]);

            if (null !== $violations && $violations->count() > 0) {
                $allViolations->addAll($violations);
            }
        }

        return $allViolations;
    }

    /**
     * @param AttributeInterface $attribute
     * @param array              $data
     */
    protected function updateAttribute(AttributeInterface $attribute, array $data)
    {
        if (isset($data['number_min'])) {
            $data['number_min'] = $this->numberLocalizer->delocalize($data['number_min'], [
                'locale' => $this->userContext->getUiLocale()->getCode()
            ]);
        }

        if (isset($data['number_max'])) {
            $data['number_max'] = $this->numberLocalizer->delocalize($data['number_max'], [
                'locale' => $this->userContext->getUiLocale()->getCode()
            ]);
        }

        $this->updater->update($attribute, $data);
    }

    /**
     * List attribute axes
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listAxesAction(Request $request)
    {
        $locale = $request->get('locale');
        $attributeAxes = $this->attributeRepository->getAxesQuery($locale)->getArrayResult();

        return new JsonResponse($attributeAxes);
    }

    private function channelCodesUsedAsConversionUnit(string $code): array
    {
        // TODO Merge Remove this on master
        // TODO This method can be updated with a real SQL query (not in 2.3 because we can't filter on JSON columns)
        if (null === $this->channelRepository) {
            return [];
        }
        $channelCodes = [];
        foreach ($this->channelRepository->findAll() as $channel) {
            $attributeCodes = array_keys($channel->getConversionUnits());
            if (in_array($code, $attributeCodes)) {
                $channelCodes[] = $channel->getCode();
            }
        }

        return $channelCodes;
    }
}
