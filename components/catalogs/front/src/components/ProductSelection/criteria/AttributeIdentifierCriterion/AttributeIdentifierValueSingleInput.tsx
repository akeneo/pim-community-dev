import React, {FC} from 'react';
import {TextInput} from 'akeneo-design-system';
import {AttributeIdentifierCriterionState} from './types';

type Props = {
    state: AttributeIdentifierCriterionState;
    onChange: (state: AttributeIdentifierCriterionState) => void;
    isInvalid: boolean;
};

const AttributeIdentifierValueSingleInput: FC<Props> = ({state, onChange, isInvalid}) => {
    return (
        <TextInput
            onChange={v => onChange({...state, value: v})}
            value={state.value.toString()}
            invalid={isInvalid}
            data-testid='value'
        />
    );
};

export {AttributeIdentifierValueSingleInput};
