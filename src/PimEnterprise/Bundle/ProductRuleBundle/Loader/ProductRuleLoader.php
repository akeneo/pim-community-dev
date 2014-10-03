<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductRuleBundle\Loader;

use Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryBuilderInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\ProductRuleBundle\Model\ProductRunnableRuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Loader\LoaderInterface;

class ProductRuleLoader implements LoaderInterface
{
    /** @var string */
    protected $runnableClass;

    /** @var ProductQueryBuilderInterface */
    protected $pqb;

    /** @var ProductRepositoryInterface */
    protected $repo;

    /**
     * @param string $runnableClass
     */
    public function __construct($runnableClass, ProductQueryBuilderInterface $pqb, ProductRepositoryInterface $repo)
    {
        $this->runnableClass = $runnableClass;
        $this->pqb = $pqb;
        $this->repo = $repo;
    }

    /**
     * {@inheritdoc}
     */
    public function load(RuleInterface $rule)
    {
        /** @var ProductRunnableRuleInterface $runnable */
        $runnable = new $this->runnableClass();

        //TODO: remove this
        $qb = $this->repo->createQueryBuilder('p');
        $this->pqb->setQueryBuilder($qb);

        $content = json_decode($rule->getContent(), true);
        foreach ($content['conditions'] as $condition) {
            echo sprintf(
                "Selecting products for rule %s (%s %s %s).\n",
                $rule->getCode(),
                $condition['field'],
                $condition['operator'],
                $condition['value']
            );
            $this->pqb->addFilter($condition['field'], $condition['operator'], $condition['value']);
        }

        $runnable->setQueryBuilder($this->pqb);

        return $runnable;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(RuleInterface $rule)
    {
        return 'product' === $rule->getType();
    }
}
