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

use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingValuesInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class FillGrantedMissingValues implements FillMissingValuesInterface
{
    /** @var FillMissingValuesInterface */
    private $baseFillMissingValues;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $localeRepository;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(
        FillMissingValuesInterface $baseFillMissingValues,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->baseFillMissingValues = $baseFillMissingValues;
        $this->attributeRepository = $attributeRepository;
        $this->localeRepository = $localeRepository;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function fromStandardFormat(array $standardFormat): array
    {
        $missingValues = $this->baseFillMissingValues->fromStandardFormat($standardFormat);

        $missingValues['values'] = $this->allowedMissingValues($missingValues['values']);

        return $missingValues;
    }

    private function allowedMissingValues(array $missingValues): array
    {
        $result = [];

        foreach ($missingValues as $attributeCode => $values) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            if (null !== $attribute && $this->authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attribute)) {
                foreach ($values as $value) {
                    $locale = $this->localeRepository->findOneByIdentifier($value['locale']);
                    if (null === $locale || $this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale)) {
                        $result[$attributeCode][] = $value;
                    }
                }
            }
        }

        return $result;
    }
}
