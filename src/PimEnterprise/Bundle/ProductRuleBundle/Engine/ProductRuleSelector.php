<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductRuleBundle\Engine;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryFactory;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\SelectorInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;

/**
 * Selects subjects impacted by a rule.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleSelector implements SelectorInterface
{
    /** @var string */
    protected $subjectSetClass;

    /** @var ProductQueryFactory */
    protected $productQueryFactory;

    /** @var ProductRepositoryInterface */
    protected $repo;

    /**
     * @param string                     $subjectSetClass
     * @param ProductQueryFactory        $productQueryFactory
     * @param ProductRepositoryInterface $repo
     */
    public function __construct(
        $subjectSetClass,
        ProductQueryFactory $productQueryFactory,
        ProductRepositoryInterface $repo
    ) {
        $this->subjectSetClass     = $subjectSetClass;
        $this->productQueryFactory = $productQueryFactory;
        $this->repo                = $repo;
    }

    /**
     * {@inheritdoc}
     */
    public function select(RuleInterface $rule)
    {
        /** @var RuleSubjectSetInterface $subjectSet */
        $subjectSet = new $this->subjectSetClass();

        $start = microtime(true);
        $pqb = $this->productQueryFactory->create(/*['default_locale' => 'en_US', 'default_scope' => 'ecommerce']*/);

        $content = json_decode($rule->getContent(), true);
        foreach ($content['conditions'] as $condition) {
            echo sprintf(
                "Selecting products for rule %s (%s %s %s).\n",
                $rule->getCode(),
                $condition['field'],
                $condition['operator'],
                is_array($condition['value']) ? implode(', ', $condition['value']) : $condition['value']
            );
            $pqb->addFilter($condition['field'], $condition['operator'], $condition['value']);
        }

        $products = $pqb->getQueryBuilder()->getQuery()->execute();

        $subjectSet->setCode($rule->getCode());
        $subjectSet->setType('product');
        $subjectSet->setSubjects($products);

        echo sprintf("Done : %sms\n", round((microtime(true) - $start) * 100));

        return $subjectSet;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(RuleInterface $rule)
    {
        return 'product' === $rule->getType();
    }
}
