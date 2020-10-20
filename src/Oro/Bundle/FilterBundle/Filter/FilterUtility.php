<?php

namespace Oro\Bundle\FilterBundle\Filter;

class FilterUtility
{
    const CONDITION_OR = 'OR';
    const CONDITION_AND = 'AND';

    const CONDITION_KEY = 'filter_condition';
    const BY_HAVING_KEY = 'filter_by_having';
    const ENABLED_KEY = 'enabled';
    const TYPE_KEY = 'type';
    const FRONTEND_TYPE_KEY = 'ftype';
    const DATA_NAME_KEY = 'data_name';
    const FORM_OPTIONS_KEY = 'options';

    public function getParamMap()
    {
        return [self::FRONTEND_TYPE_KEY => self::TYPE_KEY];
    }

    public function getExcludeParams()
    {
        return [self::DATA_NAME_KEY, self::FORM_OPTIONS_KEY];
    }
}
