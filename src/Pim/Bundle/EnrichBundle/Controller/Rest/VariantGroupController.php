<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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

    /**
     * @param EntityRepository    $variantGroupRepo
     * @param NormalizerInterface $normalizer
     */
    public function __construct(EntityRepository $variantGroupRepo, NormalizerInterface $normalizer)
    {
        $this->variantGroupRepo = $variantGroupRepo;
        $this->normalizer       = $normalizer;
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
        $variantGroup = $this->variantGroupRepo->findOneByCode($identifier);

        if (!$variantGroup) {
            throw new NotFoundHttpException(sprintf('Variant group with code "%s" not found', $identifier));
        }

        return new JsonResponse(
            $this->normalizer->normalize($variantGroup, 'internal_api', ['with_variant_group_values' => true])
        );
    }
}
