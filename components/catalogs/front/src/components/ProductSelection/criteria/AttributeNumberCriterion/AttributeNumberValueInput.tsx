import React, {FC} from 'react';
import {TextInput} from 'akeneo-design-system';
import {AttributeNumberCriterionState} from './types';
import {useNumberValue} from '../../hooks/useNumberValue';

type Props = {
    state: AttributeNumberCriterionState;
    onChange: (state: AttributeNumberCriterionState) => void;
    isInvalid: boolean;
};

const AttributeNumberValueInput: FC<Props> = ({state, onChange, isInvalid}) => {
    const [value, onValueChange] = useNumberValue(state.value, value => onChange({...state, value: value}));

    return <TextInput onChange={onValueChange} value={value} invalid={isInvalid} data-testid='value' />;
};

export {AttributeNumberValueInput};
