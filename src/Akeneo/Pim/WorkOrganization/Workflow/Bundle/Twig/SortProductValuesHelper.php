<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Twig;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * Helper for sorting product values
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class SortProductValuesHelper
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    public function __construct(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Sorts the provided values by attribute group and sort order
     *
     * @param \Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface[] $values
     *
     * @return array
     */
    public function sort(array $values)
    {
        usort(
            $values,
            function ($first, $second) {
                $firstAttribute = $this->attributeRepository->findOneByIdentifier($first->getAttributeCode());
                $secondAttribute = $this->attributeRepository->findOneByIdentifier($second->getAttributeCode());

                $firstGroupOrder = $firstAttribute->getGroup()->getSortOrder();
                $secondGroupOrder = $secondAttribute->getGroup()->getSortOrder();

                if ($firstGroupOrder !== $secondGroupOrder) {
                    return $firstGroupOrder > $secondGroupOrder ? 1 : -1;
                }

                $firstAttrOrder = $firstAttribute->getSortOrder();
                $secondAttrOrder = $secondAttribute->getSortOrder();

                return $firstAttrOrder === $secondAttrOrder ? 0 : ($firstAttrOrder > $secondAttrOrder ? 1 : -1);
            }
        );

        $sortedValues = [];

        foreach ($values as $value) {
            $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());
            $group = $attribute->getGroup();

            $sortedValues[$group->getCode()]['groupLabel'] = $group->getLabel();
            $sortedValues[$group->getCode()]['values'][] = $value;
        }

        return $sortedValues;
    }
}
