<?php

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Controller\Dashboard;

use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FindFamiliesController
{
    public function __construct(
        private SearchableRepositoryInterface $familySearchableRepository,
        private NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $identifiers = $request->query->get('identifiers') ?? [];
        $families = $this->familySearchableRepository->findBySearch(null, [
            'identifiers' => $identifiers,
            'limit' => count($identifiers),
        ]);

        $normalizedFamilies = [];
        foreach ($families as $family) {
            // PIM-10633: force the family code in lowercase
            $familyCode = strtolower($family->getCode());
            $normalizedFamilies[$familyCode] = [
                ...$this->normalizer->normalize($family, 'internal_api', ['expanded' => true]),
                'code' => $familyCode,
            ];
        }

        return new JsonResponse($normalizedFamilies);
    }
}
