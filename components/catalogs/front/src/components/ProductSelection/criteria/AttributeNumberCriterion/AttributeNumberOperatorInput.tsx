import React, {FC, useCallback} from 'react';
import {SelectInput} from 'akeneo-design-system';
import {Operator} from '../../models/Operator';
import {useOperatorTranslator} from '../../hooks/useOperatorTranslator';
import {AttributeNumberCriterionOperator, AttributeNumberCriterionState} from './types';

type Props = {
    state: AttributeNumberCriterionState;
    onChange: (state: AttributeNumberCriterionState) => void;
    isInvalid: boolean;
};

const AttributeNumberOperatorInput: FC<Props> = ({state, onChange, isInvalid}) => {
    const translateOperator = useOperatorTranslator();

    const handleChange = useCallback(
        (newValue: string) => {
            const operator = newValue as AttributeNumberCriterionOperator;

            onChange({
                ...state,
                operator: operator,
                value: [Operator.IS_EMPTY, Operator.IS_NOT_EMPTY].includes(operator) ? null : state.value,
            });
        },
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
                Operator.LOWER_OR_EQUAL_THAN,
                Operator.GREATER_THAN,
                Operator.GREATER_OR_EQUAL_THAN,
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

export {AttributeNumberOperatorInput};
