import React, {FC} from 'react';
import {TextInput} from 'akeneo-design-system';
import {AttributeNumberCriterionState} from './types';
import {parseInputNumberValue} from '../../utils/parseInputNumberValue';

type Props = {
    state: AttributeNumberCriterionState;
    onChange: (state: AttributeNumberCriterionState) => void;
    isInvalid: boolean;
};

const AttributeNumberValueInput: FC<Props> = ({state, onChange, isInvalid}) => {
    return (
        <TextInput
            onChange={v => onChange({...state, value: parseInputNumberValue(v)})}
            value={state.value?.toString()}
            invalid={isInvalid}
            data-testid='value'
        />
    );
};

export {AttributeNumberValueInput};
