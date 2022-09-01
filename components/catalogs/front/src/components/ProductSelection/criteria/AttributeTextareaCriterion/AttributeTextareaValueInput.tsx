import React, {FC} from 'react';
import {TextInput} from 'akeneo-design-system';
import {AttributeTextareaCriterionState} from './types';

type Props = {
    state: AttributeTextareaCriterionState;
    onChange: (state: AttributeTextareaCriterionState) => void;
    isInvalid: boolean;
};

const AttributeTextareaValueInput: FC<Props> = ({state, onChange, isInvalid}) => {
    return (
        <TextInput
            onChange={v => onChange({...state, value: v})}
            value={state.value.toString()}
            invalid={isInvalid}
            data-testid='value'
        />
    );
};

export {AttributeTextareaValueInput};
