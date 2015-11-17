<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Family controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyController
{
    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param EntityRepository    $familyRepository
     * @param NormalizerInterface $normalizer
     */
    public function __construct(EntityRepository $familyRepository, NormalizerInterface $normalizer)
    {
        $this->familyRepository = $familyRepository;
        $this->normalizer       = $normalizer;
    }

    /**
     * Get the family collection
     *
     * @return JsonResponse
     */
    public function indexAction()
    {
        $rawFamiliesTrans = $this->familyRepository->getFamiliesTranslationsAsArray();
        $rawFamiliesAttr  = $this->familyRepository->getFamiliesAttributeCodesAsArray();

        $normalizedFamilies = [];
        foreach ($rawFamiliesTrans as $familyTranslation) {
            $normalizedFamilies[$familyTranslation['code']]['label'][$familyTranslation['locale']] =
                $familyTranslation['label'];
            $normalizedFamilies[$familyTranslation['code']]['code'] = $familyTranslation['code'];
        }

        foreach ($rawFamiliesAttr as $familyAttributes) {
            $normalizedFamilies[$familyAttributes['code']]['attributes'] = explode(
                ',',
                $familyAttributes['attributes']
            );

            if (!isset($normalizedFamilies[$familyAttributes['code']]['label'])) {
                $normalizedFamilies[$familyAttributes['code']]['label'] = [];
            }

            if (!isset($normalizedFamilies[$familyAttributes['code']]['code'])) {
                $normalizedFamilies[$familyAttributes['code']]['code'] = $familyAttributes['code'];
            }
        }

        return new JsonResponse($normalizedFamilies);
    }
}
