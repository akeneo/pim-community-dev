import React, {FC, useCallback} from 'react';
import {SelectInput} from 'akeneo-design-system';
import {Operator} from '../../models/Operator';
import {useOperatorTranslator} from '../../hooks/useOperatorTranslator';
import {AttributeIdentifierCriterionOperator, AttributeIdentifierCriterionState} from './types';

type Props = {
    state: AttributeIdentifierCriterionState;
    onChange: (state: AttributeIdentifierCriterionState) => void;
    isInvalid: boolean;
};

const changeOperator = (
    state: AttributeIdentifierCriterionState,
    operator: AttributeIdentifierCriterionOperator
): AttributeIdentifierCriterionState => {
    let value = state.value;

    if ([Operator.IN_LIST, Operator.NOT_IN_LIST].includes(operator) && !Array.isArray(value)) {
        value = [];
    }

    if (
        [
            Operator.EQUALS,
            Operator.NOT_EQUAL,
            Operator.CONTAINS,
            Operator.DOES_NOT_CONTAIN,
            Operator.STARTS_WITH,
        ].includes(operator) &&
        typeof value !== 'string'
    ) {
        value = '';
    }

    return {
        ...state,
        operator: operator,
        value: value,
    };
};

const AttributeIdentifierOperatorInput: FC<Props> = ({state, onChange, isInvalid}) => {
    const translateOperator = useOperatorTranslator();

    const handleChange = useCallback(
        (operator: string) => onChange(changeOperator(state, operator as AttributeIdentifierCriterionOperator)),
        [state, onChange]
    );

    return (
        <SelectInput
            emptyResultLabel=''
            openLabel=''
            value={state.operator}
            onChange={handleChange}
            clearable={false}
            invalid={isInvalid}
            data-testid='operator'
        >
            {[
                Operator.EQUALS,
                Operator.NOT_EQUAL,
                Operator.CONTAINS,
                Operator.DOES_NOT_CONTAIN,
                Operator.STARTS_WITH,
                Operator.IN_LIST,
                Operator.NOT_IN_LIST,
            ].map(operator => (
                <SelectInput.Option key={operator} value={operator} title={translateOperator(operator)}>
                    {translateOperator(operator)}
                </SelectInput.Option>
            ))}
        </SelectInput>
    );
};

export {AttributeIdentifierOperatorInput};
