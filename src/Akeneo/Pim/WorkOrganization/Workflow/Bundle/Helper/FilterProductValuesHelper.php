<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Helper;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Helper for filtering product values
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class FilterProductValuesHelper
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    public function __construct(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Filter the provided values
     * Returns values that the current user is allowed to see
     * If locale is specified, only values in this locale are returned
     *
     * @param ValueInterface[] $values
     * @param string|null      $locale
     *
     * @return ValueInterface[]
     */
    public function filter(array $values, $locale = null)
    {
        $filteredValues = [];

        foreach ($values as $value) {
            $attributeCode = $value->getAttributeCode();
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

            if (null !== $locale && $attribute->isLocalizable() && $value->getLocaleCode() !== $locale) {
                continue;
            }

            if (true === $this->authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attribute->getGroup())) {
                $filteredValues[] = $value;
            }
        }

        return $filteredValues;
    }
}
