import React, {FC} from 'react';
import {SelectInput} from 'akeneo-design-system';
import {AttributeYesNoCriterionState} from './types';
import {useTranslate} from '@akeneo-pim-community/shared';

type Props = {
    state: AttributeYesNoCriterionState;
    onChange: (state: AttributeYesNoCriterionState) => void;
    isInvalid: boolean;
};

const AttributeYesNoValueInput: FC<Props> = ({state, onChange, isInvalid}) => {
    const translate = useTranslate();

    return (
        <SelectInput
            emptyResultLabel=''
            openLabel=''
            value={null !== state.value ? state.value.toString() : null}
            onChange={v => onChange({...state, value: v === 'true'})}
            clearable={false}
            invalid={isInvalid}
            data-testid='value'
        >
            <SelectInput.Option value='true'>
                {translate('akeneo_catalogs.product_selection.criteria.yesNo.yes')}
            </SelectInput.Option>
            <SelectInput.Option value='false'>
                {translate('akeneo_catalogs.product_selection.criteria.yesNo.no')}
            </SelectInput.Option>
        </SelectInput>
    );
};

export {AttributeYesNoValueInput};
