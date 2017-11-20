<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\FamilySearchableRepository;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Factory\FamilyFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariant;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Pim\Component\Catalog\Updater\FamilyUpdater;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Family controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyController
{
    const FAMILY_VARIANTS_LIMIT = 20;

    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var FamilySearchableRepository */
    protected $familySearchableRepo;

    /** @var FamilyFactory */
    protected $familyFactory;

    /** @var FamilyUpdater */
    protected $updater;

    /** @var SaverInterface */
    protected $saver;

    /** @var RemoverInterface */
    protected $remover;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var SecurityFacade */
    protected $securityFacade;

    protected $attributeFields = [
        'attributes',
        'attribute_requirements',
    ];

    protected $propertiesFields = [
        'code',
        'attribute_as_label',
        'attribute_as_image',
        'labels',
    ];

    /** @var NormalizerInterface */
    protected $constraintViolationNormalizer;

    /**
     * @param FamilyRepositoryInterface  $familyRepository
     * @param NormalizerInterface        $normalizer
     * @param FamilySearchableRepository $familySearchableRepo
     * @param FamilyUpdater              $updater
     * @param SaverInterface             $saver
     * @param RemoverInterface           $remover
     * @param ValidatorInterface         $validator
     * @param SecurityFacade             $securityFacade
     * @param FamilyFactory              $familyFactory
     * @param NormalizerInterface          $constraintViolationNormalizer
     */
    public function __construct(
        FamilyRepositoryInterface $familyRepository,
        NormalizerInterface $normalizer,
        FamilySearchableRepository $familySearchableRepo,
        FamilyUpdater $updater,
        SaverInterface $saver,
        RemoverInterface $remover,
        ValidatorInterface $validator,
        SecurityFacade $securityFacade,
        FamilyFactory $familyFactory,
        NormalizerInterface $constraintViolationNormalizer
    ) {
        $this->familyRepository = $familyRepository;
        $this->normalizer = $normalizer;
        $this->familySearchableRepo = $familySearchableRepo;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->remover = $remover;
        $this->validator = $validator;
        $this->securityFacade = $securityFacade;
        $this->familyFactory = $familyFactory;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
    }

    /**
     * Get the family collection
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function indexAction(Request $request)
    {
        $options = $request->query->get('options', ['limit' => 20]);

        if ($request->query->has('identifiers')) {
            $options['identifiers'] = explode(',', $request->query->get('identifiers'));
        }

        $families = $this->familySearchableRepo->findBySearch(
            $request->query->get('search'),
            $options
        );

        $normalizedFamilies = [];
        foreach ($families as $family) {
            $normalizedFamilies[$family->getCode()] = $this->normalizer->normalize($family, 'internal_api');
        }

        return new JsonResponse($normalizedFamilies);
    }

    /**
     * Get a single family
     *
     * @param int $identifier
     *
     * @return JsonResponse
     */
    public function getAction(Request $request, $identifier)
    {
        $family = $this->getFamily($identifier);
        $applyFilters = $request->query->getBoolean('apply_filters', true);

        return new JsonResponse(
            $this->normalizer->normalize(
                $family,
                'internal_api',
                ['apply_filters' => $applyFilters]
            )
        );
    }

    /**
     * Updates family
     *
     * @param Request $request
     * @param string  $code
     *
     * @return JsonResponse
     */
    public function putAction(Request $request, $code)
    {
        if (!$this->securityFacade->isGranted('pim_enrich_family_edit_properties') &&
            !$this->securityFacade->isGranted('pim_enrich_family_edit_attributes')
        ) {
            throw new AccessDeniedException();
        }

        $family = $this->getFamily($code);

        return $this->saveFamily($request, $family);
    }

    /**
     * Removes given family
     *
     * @AclAncestor("pim_enrich_family_remove")
     *
     * @param Request $request
     * @param string  $code
     *
     * @return JsonResponse
     */
    public function removeAction(Request $request, $code)
    {
        $family = $this->getFamily($code);
        $this->remover->remove($family);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param string  $code
     *
     * @return JsonResponse
     */
    public function getAvailableAxesAction(string $code): JsonResponse
    {
        $family = $this->getFamily($code);
        $allowedTypes = FamilyVariant::getAvailableAxesAttributeTypes();

        $availableAxes = $family->getAttributes()->filter(function (AttributeInterface $attribute) use ($allowedTypes) {
            return in_array($attribute->getType(), $allowedTypes);
        });

        $normalizedAvailableAttributes = [];
        foreach ($availableAxes as $availableAxis) {
            $normalizedAvailableAttributes[] = $this->normalizer->normalize($availableAxis, 'internal_api');
        }

        return new JsonResponse($normalizedAvailableAttributes);
    }

    /**
     * Gets family
     *
     * @param string $code
     *
     * @throws HttpExceptionInterface
     *
     * @return FamilyInterface|object
     */
    protected function getFamily($code)
    {
        $family = $this->familyRepository->findOneBy(['code' => $code]);

        if (null === $family) {
            throw new NotFoundHttpException(
                sprintf('Family with code %s does not exist.', $code)
            );
        }

        return $family;
    }

    /**
     * Saves family
     *
     * @param Request         $request
     * @param FamilyInterface $family
     *
     * @return JsonResponse
     */
    protected function saveFamily(Request $request, FamilyInterface $family)
    {
        $data = json_decode($request->getContent(), true);

        if (!$this->securityFacade->isGranted('pim_enrich_family_edit_properties')) {
            $data = array_filter($data, function ($value, $key) {
                return !in_array($key, $this->propertiesFields);
            });
        }

        if (!$this->securityFacade->isGranted('pim_enrich_family_edit_attributes')) {
            $data = array_filter($data, function ($value, $key) {
                return !in_array($key, $this->attributeFields);
            });
        }

        $this->updater->update($family, $data);

        $violations = $this->validator->validate($family);

        if (0 < $violations->count()) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = [
                    'message' => $violation->getMessage()
                ];
            }

            return new JsonResponse($errors, Response::HTTP_CONFLICT);
        }

        $this->saver->save($family);

        return new JsonResponse(
            $this->normalizer->normalize(
                $family,
                'internal_api'
            )
        );
    }

    /**
     * Creates family
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        $family = $this->familyFactory->create();
        $this->updater->update($family, json_decode($request->getContent(), true));
        $violations = $this->validator->validate($family);

        $normalizedViolations = [];
        foreach ($violations as $violation) {
            $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                $violation,
                'internal_api',
                ['family' => $family]
            );
        }

        if (count($normalizedViolations) > 0) {
            return new JsonResponse(['values' => $normalizedViolations], 400);
        }

        $this->saver->save($family);

        return new JsonResponse($this->normalizer->normalize(
            $family,
            'internal_api'
        ));
    }


    /**
     * Gets families with familyVariants
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getWithVariantsAction(Request $request): JsonResponse
    {
        $search = $request->query->get('search');
        $options = $request->query->get('options');

        $families = $this->familyRepository->getWithVariants($search, $options, self::FAMILY_VARIANTS_LIMIT);

        $normalizedFamilies = [];
        foreach ($families as $family) {
            $normalizedFamilies[$family->getCode()] = $this->normalizer->normalize($family, 'internal_api');
        }

        return new JsonResponse($normalizedFamilies);
    }
}
