<?php

declare(strict_types=1);

namespace Pim\Bundle\ApiBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Component\Api\Repository\ApiResourceRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantController
{
    /** @var ApiResourceRepositoryInterface */
    protected $familyRepository;

    /** @var ApiResourceRepositoryInterface */
    protected $familyVariantRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param ApiResourceRepositoryInterface $familyRepository
     * @param ApiResourceRepositoryInterface $familyVariantRepository
     * @param NormalizerInterface            $normalizer
     */
    public function __construct(
        ApiResourceRepositoryInterface $familyRepository,
        ApiResourceRepositoryInterface $familyVariantRepository,
        NormalizerInterface $normalizer
    ) {
        $this->familyRepository = $familyRepository;
        $this->familyVariantRepository = $familyVariantRepository;
        $this->normalizer = $normalizer;
    }

    /**
     * @param Request $request
     * @param string  $familyCode
     * @param string  $familyVariantCode
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_api_family_list")
     */
    public function getAction(Request $request, string $familyCode, string $familyVariantCode)
    {
        $family = $this->familyRepository->findOneByIdentifier($familyCode);
        if (null === $family) {
            throw new NotFoundHttpException(sprintf('Family "%s" does not exist.', $familyCode));
        }

        $familyVariant = $this->familyVariantRepository->findOneByIdentifier($familyVariantCode);
        if (null === $familyVariant || $familyVariant->getFamily()->getCode() !== $familyCode) {
            throw new NotFoundHttpException(
                sprintf(
                    'Family variant "%s" does not exist or is not a variant of the family "%s".',
                    $familyVariantCode,
                    $familyCode
                )
            );
        }

        $familyVariantApi = $this->normalizer->normalize($familyVariant, 'external_api');

        return new JsonResponse($familyVariantApi);
    }
}
