<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Controller\InternalApi;

use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Family variant controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantController
{
    /** @var FamilyVariantRepositoryInterface */
    protected $familyVariantRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var SimpleFactoryInterface */
    protected $familyVariantFactory;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var NormalizerInterface */
    protected $constraintViolationNormalizer;

    /** @var SaverInterface */
    protected $saver;

    /** @var RemoverInterface */
    private $remover;

    /** @var SearchableRepositoryInterface */
    protected $searchableRepository;

    /**
     * @param FamilyVariantRepositoryInterface $familyVariantRepository
     * @param NormalizerInterface              $normalizer
     * @param SimpleFactoryInterface           $familyVariantFactory
     * @param ObjectUpdaterInterface           $updater
     * @param ValidatorInterface               $validator
     * @param NormalizerInterface              $constraintViolationNormalizer
     * @param SaverInterface                   $saver
     * @param RemoverInterface                 $remover
     * @param SearchableRepositoryInterface    $searchableRepository
     */
    public function __construct(
        FamilyVariantRepositoryInterface $familyVariantRepository,
        NormalizerInterface $normalizer,
        SimpleFactoryInterface $familyVariantFactory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        NormalizerInterface $constraintViolationNormalizer,
        SaverInterface $saver,
        RemoverInterface $remover,
        SearchableRepositoryInterface $searchableRepository
    ) {
        $this->familyVariantRepository = $familyVariantRepository;
        $this->normalizer = $normalizer;
        $this->familyVariantFactory = $familyVariantFactory;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
        $this->saver = $saver;
        $this->remover = $remover;
        $this->searchableRepository = $searchableRepository;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function indexAction(Request $request): JsonResponse
    {
        $options = $request->query->get('options', ['limit' => 20]);

        if ($request->query->has('family_id')) {
            $options['familyId'] = $request->query->get('family_id');
        }

        if ($request->query->has('identifiers')) {
            $options['identifiers'] = explode(',', $request->query->get('identifiers'));
        }

        $familyVariants = $this->searchableRepository->findBySearch(
            $request->query->get('search'),
            $options
        );

        $normalizedFamilyVariants = [];
        foreach ($familyVariants as $familyVariant) {
            $normalizedFamilyVariants[$familyVariant->getCode()] = $this->normalizer->normalize(
                $familyVariant,
                'internal_api'
            );
        }

        return new JsonResponse($normalizedFamilyVariants);
    }

    /**
     * Get a single familyVariant variant
     *
     * @param string $identifier
     *
     * @return JsonResponse
     */
    public function getAction(string $identifier): JsonResponse
    {
        $familyVariant = $this->getFamilyVariant($identifier);

        return new JsonResponse(
            $this->normalizer->normalize(
                $familyVariant,
                'internal_api'
            )
        );
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $familyVariant = $this->familyVariantFactory->create();
        $content = json_decode($request->getContent(), true);

        return $this->saveFamilyVariant($familyVariant, $content);
    }

    /**
     * @param Request $request
     * @param string  $identifier
     *
     * @return Response
     */
    public function putAction(Request $request, string $identifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $familyVariant = $this->getFamilyVariant($identifier);
        $content = json_decode($request->getContent(), true);

        return $this->saveFamilyVariant($familyVariant, $content);
    }

    /**
     * @param Request $request
     * @param string  $familyVariantCode
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_enrich_family_variant_remove")
     */
    public function removeAction(Request $request, string $familyVariantCode)
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['message' => 'An error occurred.', 'global' => true], Response::HTTP_BAD_REQUEST);
        }

        $familyVariant = $this->getFamilyVariant($familyVariantCode);
        try {
            $this->remover->remove($familyVariant);
        } catch (\LogicException $e) {
            return new JsonResponse(
                [
                    'message' => sprintf(
                        'Cannot remove family variant "%s" as it is used by some product models',
                        $familyVariant->getCode()
                    ),
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Gets familyVariant using its code
     *
     * @param string $code
     *
     * @return FamilyVariantInterface
     */
    protected function getFamilyVariant(string $code): FamilyVariantInterface
    {
        $familyVariant = $this->familyVariantRepository->findOneBy(['code' => $code]);

        if (null === $familyVariant) {
            throw new NotFoundHttpException(
                sprintf('Family variant with code %s does not exist.', $code)
            );
        }

        return $familyVariant;
    }

    /**
     * Handle the save action for the family variant entity
     *
     * @param FamilyVariantInterface $familyVariant
     * @param array                  $content
     *
     * @return JsonResponse
     */
    protected function saveFamilyVariant(FamilyVariantInterface $familyVariant, array $content): JsonResponse
    {
        $this->updater->update($familyVariant, $content);
        $violations = $this->validator->validate($familyVariant);

        $normalizedViolations = [];
        foreach ($violations as $violation) {
            $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                $violation,
                'internal_api',
                ['family_variant' => $familyVariant]
            );
        }

        if (count($violations) > 0) {
            return new JsonResponse($normalizedViolations, 400);
        }

        $this->saver->save($familyVariant);

        return new JsonResponse(
            $this->normalizer->normalize(
                $familyVariant,
                'internal_api'
            )
        );
    }
}
