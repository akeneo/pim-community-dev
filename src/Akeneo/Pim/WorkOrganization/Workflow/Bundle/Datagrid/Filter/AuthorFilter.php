<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface as PimFilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\AjaxChoiceFilter;
use Symfony\Component\Form\FormFactoryInterface;
use Webmozart\Assert\Assert;

/**
 * Author filter for an Elasticsearch query.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AuthorFilter extends AjaxChoiceFilter
{
    /** @var FieldFilterInterface */
    private $authorFilter;

    /**
     * @param FormFactoryInterface $factory
     * @param FilterUtility        $util
     * @param FieldFilterInterface $authorFilter
     */
    public function __construct(FormFactoryInterface $factory, FilterUtility $util, FieldFilterInterface $authorFilter)
    {
        parent::__construct($factory, $util);

        $this->authorFilter = $authorFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        $field = $this->get(ProductDraftFilterUtility::DATA_NAME_KEY);
        $operator = $this->getOperator($data['type']);
        $value = $data['value'];

        Assert::implementsInterface($ds, PimFilterDatasourceAdapterInterface::class);
        $this->authorFilter->setQueryBuilder($ds->getQueryBuilder());
        $this->authorFilter->addFieldFilter($field, $operator, $value);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        $metadata = parent::getMetadata();

        $metadata['emptyChoice'] = true;
        $metadata[FilterUtility::TYPE_KEY] = 'select2-rest-choice';

        return $metadata;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormOptions()
    {
        return array_merge(
            parent::getFormOptions(),
            ['choice_url' => 'pimee_workflow_author_draft_rest']
        );
    }
}
