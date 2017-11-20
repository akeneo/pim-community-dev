<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Repository\FamilyVariantRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @param FamilyVariantRepositoryInterface $familyVariantRepository
     * @param NormalizerInterface              $normalizer
     * @param SimpleFactoryInterface           $familyVariantFactory
     * @param ObjectUpdaterInterface           $updater
     * @param ValidatorInterface               $validator
     * @param NormalizerInterface              $constraintViolationNormalizer
     * @param SaverInterface                   $saver
     */
    public function __construct(
        FamilyVariantRepositoryInterface $familyVariantRepository,
        NormalizerInterface $normalizer,
        SimpleFactoryInterface $familyVariantFactory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        NormalizerInterface $constraintViolationNormalizer,
        SaverInterface $saver
    ) {
        $this->familyVariantRepository = $familyVariantRepository;
        $this->normalizer = $normalizer;
        $this->familyVariantFactory = $familyVariantFactory;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
        $this->saver = $saver;
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
     * @return JsonResponse
     */
    public function createAction(Request $request): JsonResponse
    {
        $familyVariant = $this->familyVariantFactory->create();
        $content = json_decode($request->getContent(), true);

        return $this->saveFamilyVariant($familyVariant, $content);
    }

    /**
     * @param Request $request
     * @param string  $identifier
     *
     * @return JsonResponse
     */
    public function putAction(Request $request, string $identifier): JsonResponse
    {
        $familyVariant = $this->getFamilyVariant($identifier);
        $content = json_decode($request->getContent(), true);

        return $this->saveFamilyVariant($familyVariant, $content);
    }

    /**
     * Gets familyVariant
     *
     * @param string $code
     *
     * @throws HttpExceptionInterface
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
