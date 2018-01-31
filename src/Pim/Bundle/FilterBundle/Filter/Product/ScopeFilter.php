<?php

namespace Pim\Bundle\FilterBundle\Filter\Product;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\FilterBundle\Form\Type\Filter\ScopeFilterType;
use Pim\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Scope filter
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopeFilter extends ChoiceFilter
{
    /**
     * @var CatalogContext
     */
    protected $catalogContext;

    /**
     * @var UserContext
     */
    protected $userContext;

    /**
     * Constructor
     *
     * @param FormFactoryInterface $factory
     * @param FilterUtility        $util
     * @param CatalogContext       $catalogContext
     * @param UserContext          $userContext
     */
    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        CatalogContext $catalogContext,
        UserContext $userContext
    ) {
        parent::__construct($factory, $util);

        $this->catalogContext = $catalogContext;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function init($name, array $params)
    {
        parent::init($name, $params);
        // TODO : useful ? I would expect that it's configured in datasource itself
        $this->catalogContext->setScopeCode($this->userContext->getUserChannelCode());
    }

    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $channelCode = $this->parseData($data);
        if (!$channelCode) {
            $channelCode = $this->userContext->getUserChannelCode();
        }

        $this->catalogContext->setScopeCode($channelCode);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        $metadata = parent::getMetadata();

        $defaultScope = $this->userContext->getUserChannel();
        $metadata['populateDefault'] = false;
        unset($metadata['placeholder']);

        return $metadata;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return ScopeFilterType::class;
    }

    /**
     * @param mixed $data
     *
     * @return string|bool
     */
    protected function parseData($data)
    {
        if (!is_array($data) || empty($data['value'])) {
            return false;
        }

        return $data['value'];
    }
}
