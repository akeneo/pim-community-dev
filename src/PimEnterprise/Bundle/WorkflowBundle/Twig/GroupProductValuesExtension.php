<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Twig;

use Doctrine\Common\Collections\Collection;
use PimEnterprise\Bundle\WorkflowBundle\Helper\FilterProductValuesHelper;
use PimEnterprise\Bundle\WorkflowBundle\Helper\SortProductValuesHelper;

/**
 * Twig extension to group and sort product values to prepare them for display
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class GroupProductValuesExtension extends \Twig_Extension
{
    /** @var FilterProductValuesHelper */
    protected $filterHelper;

    /** @var SortProductValuesHelper */
    protected $sortHelper;

    /**
     * @param FilterProductValuesHelper $filterHelper
     * @param SortProductValuesHelper   $sortHelper
     */
    public function __construct(FilterProductValuesHelper $filterHelper, SortProductValuesHelper $sortHelper)
    {
        $this->filterHelper = $filterHelper;
        $this->sortHelper   = $sortHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pimee_workflow_group_product_values_extension';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'group_product_values',
                [$this, 'groupValues']
            ),
        ];
    }

    /**
     * Group product values
     *
     * @param Collection|\Pim\Bundle\CatalogBundle\Model\AbstractProductValue[] $values
     * @param string                                                            $locale
     *
     * @return array
     */
    public function groupValues(Collection $values, $locale = null)
    {
        $values = $values->toArray();
        $values = $this->filterHelper->filter($values, $locale);
        $values = $this->sortHelper->sort($values);

        return $values;
    }
}
