<?php

namespace Oro\Bundle\EmailBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class EmailTemplatesGridHelper
{
    /** @var array */
    protected $entityNameChoices;

    public function __construct($entitiesConfig = [])
    {
        $this->entityNameChoices = array_map(
            function ($value) {
                return isset($value['name']) ? $value['name'] : '';
            },
            $entitiesConfig
        );
    }

    /**
     * Returns callback for configuration of grid/actions visibility per row
     *
     * @return callable
     */
    public function getActionConfigurationClosure()
    {
        return function (ResultRecordInterface $record) {
            if ($record->getValue('isSystem')) {
                return array('delete' => false);
            }
        };
    }

    /**
     * Returns choice list for entityName filter
     *
     * @return array
     */
    public function getEntityNameChoices()
    {
        return $this->entityNameChoices;
    }

    /**
     * Returns email template type choice list
     *
     * @return array
     */
    public function getTypeChoices()
    {
        return [
            'html' => 'oro.email.datagrid.emailtemplate.filter.type.html',
            'txt'  => 'oro.email.datagrid.emailtemplate.filter.type.txt'
        ];
    }

    /**
     * Returns choice list for isSystem field and filter
     *
     * @return array
     */
    public function getSystemChoices()
    {
        return [
            'oro.email.datagrid.emailtemplate.filter.isSystem.no',
            'oro.email.datagrid.emailtemplate.filter.isSystem.yes'
        ];
    }
}
