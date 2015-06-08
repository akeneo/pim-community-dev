<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Controller;

use Pim\Bundle\CatalogBundle\Resolver\FQCNResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Rule relation controller
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class RuleRelationController
{
    /** @var EntityRepository */
    protected $ruleRelationRepo;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var FQCNResolver */
    protected $FQCNResolver;

    /**
     * @param EntityRepository    $ruleRelationRepo
     * @param NormalizerInterface $normalizer
     */
    public function __construct(
        EntityRepository $ruleRelationRepo,
        NormalizerInterface $normalizer,
        FQCNResolver $FQCNResolver
    ) {
        $this->ruleRelationRepo = $ruleRelationRepo;
        $this->normalizer       = $normalizer;
        $this->FQCNResolver     = $FQCNResolver;
    }

    /**
     * Get all rule relations for a given type
     * @param string $relationType
     *
     * @return JsonResponse
     */
    public function indexAction($relationType)
    {
        $ruleRelations = $this->ruleRelationRepo->findBy([
            'resourceName' => $this->FQCNResolver->getFQCN($relationType)
        ]);

        $normalizedRelations = [];
        foreach ($ruleRelations as $ruleRelation) {
            $normalizedRelations[] = $this->normalizer->normalize($ruleRelation, 'internal_api');
        }

        return new JsonResponse($normalizedRelations);
    }
}
