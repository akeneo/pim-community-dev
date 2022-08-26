import React, {FC} from 'react';
import {TextInput} from 'akeneo-design-system';
import {AttributeTextCriterionState} from './types';

type Props = {
    state: AttributeTextCriterionState;
    onChange: (state: AttributeTextCriterionState) => void;
    isInvalid: boolean;
};

const AttributeTextValueInput: FC<Props> = ({state, onChange, isInvalid}) => {
    return (
        <TextInput
            onChange={v => onChange({...state, value: v})}
            value={state.value.toString()}
            invalid={isInvalid}
            data-testid='value'
        />
    );
};

export {AttributeTextValueInput};
