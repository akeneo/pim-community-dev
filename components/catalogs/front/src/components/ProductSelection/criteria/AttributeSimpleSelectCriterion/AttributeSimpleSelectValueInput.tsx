import React, {FC, useState} from 'react';
import {MultiSelectInput} from 'akeneo-design-system';
import {AttributeSimpleSelectCriterionState} from './types';
import {useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {AttributeOption} from '../../models/AttributeOption';
import {useInfiniteAttributeOptions} from '../../hooks/useInfiniteAttributeOptions';
import {useAttributeOptionsByCodes} from '../../hooks/useAttributeOptionsByCodes';
import {useUniqueEntitiesByCode} from '../../../../hooks/useUniqueEntitiesByCode';

type Props = {
    state: AttributeSimpleSelectCriterionState;
    onChange: (state: AttributeSimpleSelectCriterionState) => void;
    isInvalid: boolean;
};

const AttributeSimpleSelectValueInput: FC<Props> = ({state, onChange, isInvalid}) => {
    const translate = useTranslate();
    const locale = useUserContext().get('catalogLocale');
    const [search, setSearch] = useState<string>();
    const {data: selection} = useAttributeOptionsByCodes(state.field, state.value, locale);
    const {data: results, fetchNextPage} = useInfiniteAttributeOptions({attribute: state.field, search, locale});
    const options = useUniqueEntitiesByCode<AttributeOption>(selection, results);

    return (
        <MultiSelectInput
            value={state.value}
            emptyResultLabel={translate('akeneo_catalogs.product_selection.criteria.attribute_option.no_matches')}
            openLabel={translate('akeneo_catalogs.product_selection.action.open')}
            removeLabel={translate('akeneo_catalogs.product_selection.action.remove')}
            placeholder={translate('akeneo_catalogs.product_selection.criteria.attribute_option.placeholder')}
            onChange={v => onChange({...state, value: v})}
            onNextPage={fetchNextPage}
            onSearchChange={setSearch}
            invalid={isInvalid}
            data-testid='value'
        >
            {options.map(option => (
                <MultiSelectInput.Option key={option.code} title={option.label} value={option.code}>
                    {option.label}
                </MultiSelectInput.Option>
            ))}
        </MultiSelectInput>
    );
};

export {AttributeSimpleSelectValueInput};
