import React, {FC, useCallback} from 'react';
import {SelectInput} from 'akeneo-design-system';
import {Operator} from '../../models/Operator';
import {useOperatorTranslator} from '../../hooks/useOperatorTranslator';
import {AttributeDateCriterionOperator, AttributeDateCriterionState} from './types';

type Props = {
    state: AttributeDateCriterionState;
    onChange: (state: AttributeDateCriterionState) => void;
    isInvalid: boolean;
};

const changeOperator = (
    state: AttributeDateCriterionState,
    operator: AttributeDateCriterionOperator
): AttributeDateCriterionState => {
    let value = state.value;

    if ([Operator.BETWEEN, Operator.NOT_BETWEEN].includes(operator) && !Array.isArray(value)) {
        value = [];
    }

    if (
        [
            Operator.EQUALS,
            Operator.NOT_EQUAL,
            Operator.LOWER_THAN,
            Operator.GREATER_THAN,
            Operator.IS_EMPTY,
            Operator.IS_NOT_EMPTY,
        ].includes(operator) &&
        typeof value !== 'string'
    ) {
        value = null;
    }

    return {
        ...state,
        operator: operator,
        value: value,
    };
};

const AttributeDateOperatorInput: FC<Props> = ({state, onChange, isInvalid}) => {
    const translateOperator = useOperatorTranslator();

    const handleChange = useCallback(
        (operator: string) => onChange(changeOperator(state, operator as AttributeDateCriterionOperator)),
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
                Operator.LOWER_THAN,
                Operator.GREATER_THAN,
                Operator.BETWEEN,
                Operator.NOT_BETWEEN,
                Operator.IS_EMPTY,
                Operator.IS_NOT_EMPTY,
            ].map(operator => (
                <SelectInput.Option key={operator} value={operator} title={translateOperator(operator)}>
                    {translateOperator(operator)}
                </SelectInput.Option>
            ))}
        </SelectInput>
    );
};

export {AttributeDateOperatorInput};
