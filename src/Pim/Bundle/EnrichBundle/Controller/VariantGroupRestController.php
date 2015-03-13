<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * VariantGroup controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupRestController
{
    protected $variantGroupRepo;
    protected $normalizer;

    public function __construct(EntityRepository $variantGroupRepo, NormalizerInterface $normalizer)
    {
        $this->variantGroupRepo = $variantGroupRepo;
        $this->normalizer       = $normalizer;
    }

    public function indexAction()
    {
        $variantGroups = $this->variantGroupRepo->getAllVariantGroups();

        $normalizedVariants = [];
        foreach ($variantGroups as $variantGroup) {
            $normalizedVariants[$variantGroup->getCode()] = $this->normalizer->normalize(
                $variantGroup,
                'json',
                ['with_variant_group_values' => true]
            );
        }

        return new JsonResponse($normalizedVariants);
    }

    public function getAction($identifier)
    {
        $variantGroup = $this->variantGroupRepo->findOneByCode($identifier);

        return new JsonResponse(
            $this->normalizer->normalize($variantGroup, 'json', ['with_variant_group_values' => true])
        );
    }
}
