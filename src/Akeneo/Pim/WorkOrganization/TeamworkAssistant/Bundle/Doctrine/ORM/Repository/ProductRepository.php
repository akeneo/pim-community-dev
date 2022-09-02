<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetGrantedCategoryCodes;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProductRepositoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Webmozart\Assert\Assert;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProductRepository implements ProductRepositoryInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $productQueryBuilderFactory;

    /** @var GetGrantedCategoryCodes */
    private $getAllViewableCategoryCodes;

    public function __construct(
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        GetGrantedCategoryCodes $getAllViewableCategoryCodes
    ) {
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->getAllViewableCategoryCodes = $getAllViewableCategoryCodes;
    }

    /**
     * {@inheritdoc}
     */
    public function findByProject(ProjectInterface $project)
    {
        $productFilters = $project->getProductFilters();

        if (null === $productFilters) {
            throw new \LogicException(sprintf('The project "%s" does not have product filters', $project->getLabel()));
        }

        $productQueryBuilder = $this->productQueryBuilderFactory->create([
            'default_locale' => $project->getLocale()->getCode(),
            'default_scope'  => $project->getChannel()->getCode(),
        ]);

        foreach ($productFilters as $productFilter) {
            if ('categories' === $productFilter['field']
                && 'IN OR UNCLASSIFIED' === $productFilter['operator']
            ) {
                continue;
            }
            if ('completeness' === $productFilter['field']) {
                $productFilter = $this->convertCompletenessFilter($productFilter);
            }

            $productQueryBuilder->addFilter($productFilter['field'], $productFilter['operator'], $productFilter['value']);
        }

        $owner = $project->getOwner();
        Assert::implementsInterface($owner, UserInterface::class);
        $categoriesCodes = $this->getAllViewableCategoryCodes->forGroupIds($owner->getGroupsIds());

        $productQueryBuilder->addFilter('categories', 'IN OR UNCLASSIFIED', $categoriesCodes, ['type_checking' => false]);
        $productQueryBuilder->addFilter('family', 'NOT EMPTY', null);

        return $productQueryBuilder->execute();
    }

    /**
     * For the completeness filter, the operators come from the "products and product-models" search.
     *  (See service pim_catalog.query.elasticsearch.filter.product_and_product_model.completeness)
     * They must be converted to operators supported by the "products only" query builder. (i.e. products and variants, without models)
     *  (See service pim_catalog.query.elasticsearch.filter.product.completeness)
     * As the filters come from a product-grid view, only the operators "AT LEAST INCOMPLETE" and "AT LEAST COMPLETE" are concerned.
     */
    private function convertCompletenessFilter(array $filter): array
    {
        if ('AT LEAST INCOMPLETE' === $filter['operator']) {
            $filter['operator'] = '<';
            $filter['value'] = 100;
        } elseif ('AT LEAST COMPLETE' === $filter['operator']) {
            $filter['operator'] = '=';
            $filter['value'] = 100;
        }

        return $filter;
    }
}
