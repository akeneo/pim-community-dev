import React, {FC} from 'react';
import {TagInput} from 'akeneo-design-system';
import {AttributeIdentifierCriterionState} from './types';

type Props = {
    state: AttributeIdentifierCriterionState;
    onChange: (state: AttributeIdentifierCriterionState) => void;
    isInvalid: boolean;
};

const AttributeIdentifierValueMultiInput: FC<Props> = ({state, onChange, isInvalid}) => {
    const value = Array.isArray(state.value) ? state.value : [];

    return (
        <TagInput
            onChange={v => onChange({...state, value: v})}
            value={value}
            invalid={isInvalid}
            data-testid='value'
        />
    );
};

export {AttributeIdentifierValueMultiInput};
