<?php

namespace Oro\Bundle\SearchBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;
use Oro\Bundle\GridBundle\Property\CallbackProperty;
use Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface;
use Oro\Bundle\SearchBundle\Query\Result\Item;

class SearchDatagridManager extends DatagridManager
{
    /**
     * @var string
     */
    protected $searchEntity = '*';

    /**
     * @var string
     */
    protected $searchString;

    /**
     * @var string
     */
    protected $itemContainerTemplate;

    /**
     * @param string $itemContainerTemplate
     */
    public function __construct($itemContainerTemplate)
    {
        $this->itemContainerTemplate = $itemContainerTemplate;
    }

    /**
     * Configure collection of field descriptions
     *
     * @param FieldDescriptionCollection $fieldCollection
     */
    protected function configureFields(FieldDescriptionCollection $fieldCollection)
    {
        $item = new FieldDescription();
        $item->setName('item');
        $item->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_HTML,
                'label'       => 'Item',
                'field_name'  => 'entity',
                'required'    => false,
                'sortable'    => false,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $templateProperty = new TwigTemplateProperty($item, $this->itemContainerTemplate);
        $item->setProperty($templateProperty);
        $fieldCollection->add($item);

        $url = new FieldDescription();
        $url->setName('url');
        $url->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'URL',
                'required'    => false,
                'sortable'    => false,
                'filterable'  => false,
                'show_filter' => false,
                'show_column' => false,
            )
        );
        $callbackProperty = new CallbackProperty(
            $url->getName(),
            function (ResultRecordInterface $record) {
                /** @var $indexerItem Item */
                if ($record->getValue('entity')) {
                    $indexerItem = $record->getValue('indexer_item');

                    return $indexerItem->getRecordUrl();
                }

                return null;
            }
        );
        $url->setProperty($callbackProperty);
        $fieldCollection->add($url);
    }

    /**
     * @return ProxyQueryInterface
     */
    protected function createQuery()
    {
        /** @var $query Query */
        $query = parent::createQuery();
        $query
            ->from($this->searchEntity)
            ->andWhere(Indexer::TEXT_ALL_DATA_FIELD, '~', $this->searchString, 'text');

        return $query;
    }

    /**
     * @return array
     */
    protected function getFilters()
    {
        return array();
    }

    /**
     * @return array
     */
    protected function getSorters()
    {
        return array();
    }

    /**
     * @return array
     */
    protected function getDefaultParameters()
    {
        return array();
    }

    /**
     * Set search entity (f.e. user, product etc.)
     *
     * @param string|null $searchEntity
     */
    public function setSearchEntity($searchEntity)
    {
        if ($searchEntity) {
            $this->searchEntity = $searchEntity;
        } else {
            $this->searchEntity = '*';
        }
    }

    /**
     * Set search string
     *
     * @param string $searchString
     */
    public function setSearchString($searchString)
    {
        $this->searchString = $searchString;
    }
}
