<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\MissingRequiredAttributesNormalizerInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class MissingRequiredAttributesNormalizer implements MissingRequiredAttributesNormalizerInterface
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $localeRepository;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $localeRepository
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->attributeRepository = $attributeRepository;
        $this->localeRepository = $localeRepository;
    }

    public function normalize(ProductCompletenessWithMissingAttributeCodesCollection $completenesses): array
    {
        $missingRequiredAttributes = [];

        foreach ($completenesses as $completeness) {
            $channelCode = $completeness->channelCode();
            $localeCode = $completeness->localeCode();

            if (!isset($missingRequiredAttributes[$channelCode])) {
                $missingRequiredAttributes[$channelCode] = [
                    'channel' => $channelCode,
                    'locales' => [],
                ];
            }

            $missingRequiredAttributes[$channelCode]['locales'][$localeCode]['missing'] = array_map(
                function (string $attributeCode): array {
                    return ['code' => $attributeCode];
                },
                $this->filterAttributeCodes($completeness->missingAttributeCodes(), $localeCode)
            );
        }

        return array_values($missingRequiredAttributes);
    }

    private function filterAttributeCodes(array $attributeCodes, string $localeCode): array
    {
        $filteredAttributeCodes = [];

        $locale = $this->localeRepository->findOneByIdentifier($localeCode);
        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            if (!$this->authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $attribute->getGroup())) {
                continue;
            }
            if ($attribute->isLocalizable() && !$this->authorizationChecker->isGranted(Attributes::EDIT_ITEMS, $locale)) {
                continue;
            }
            $filteredAttributeCodes[] = $attributeCode;
        }

        return $filteredAttributeCodes;
    }
}
