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
        $productFilers = $project->getProductFilters();

        if (null === $productFilers) {
            throw new \LogicException(sprintf('The project "%s" does not have product filters', $project->getLabel()));
        }

        $productQueryBuilder = $this->productQueryBuilderFactory->create([
            'default_locale' => $project->getLocale()->getCode(),
            'default_scope'  => $project->getChannel()->getCode(),
        ]);

        foreach ($productFilers as $productFiler) {
            if ('categories' === $productFiler['field']
                && 'IN OR UNCLASSIFIED' === $productFiler['operator']
            ) {
                continue;
            }
            $productQueryBuilder->addFilter($productFiler['field'], $productFiler['operator'], $productFiler['value']);
        }

        $owner = $project->getOwner();
        Assert::implementsInterface($owner, UserInterface::class);
        $categoriesCodes = $this->getAllViewableCategoryCodes->forGroupIds($owner->getGroupsIds());

        $productQueryBuilder->addFilter('categories', 'IN OR UNCLASSIFIED', $categoriesCodes, ['type_checking' => false]);
        $productQueryBuilder->addFilter('family', 'NOT EMPTY', null);

        return $productQueryBuilder->execute();
    }
}
