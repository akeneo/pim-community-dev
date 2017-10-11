<?php
declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Repository\FamilyVariantRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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

    /**
     * @param FamilyVariantRepositoryInterface $familyVariantRepository
     * @param NormalizerInterface              $normalizer
     */
    public function __construct(
        FamilyVariantRepositoryInterface $familyVariantRepository,
        NormalizerInterface $normalizer
    ) {
        $this->familyVariantRepository = $familyVariantRepository;
        $this->normalizer = $normalizer;
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
}
