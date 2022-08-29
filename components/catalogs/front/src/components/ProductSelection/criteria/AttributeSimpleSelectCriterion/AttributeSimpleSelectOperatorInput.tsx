import React, {FC, useCallback} from 'react';
import {SelectInput} from 'akeneo-design-system';
import {Operator} from '../../models/Operator';
import {useOperatorTranslator} from '../../hooks/useOperatorTranslator';
import {AttributeSimpleSelectCriterionOperator, AttributeSimpleSelectCriterionState} from './types';

type Props = {
    state: AttributeSimpleSelectCriterionState;
    onChange: (state: AttributeSimpleSelectCriterionState) => void;
    isInvalid: boolean;
};

const AttributeSimpleSelectOperatorInput: FC<Props> = ({state, onChange, isInvalid}) => {
    const translateOperator = useOperatorTranslator();

    const handleChange = useCallback(
        (newValue: string) => {
            const operator = newValue as AttributeSimpleSelectCriterionOperator;

            onChange({
                ...state,
                operator: operator,
                value: [Operator.IS_EMPTY, Operator.IS_NOT_EMPTY].includes(operator) ? [] : state.value,
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
            {[Operator.IN_LIST, Operator.NOT_IN_LIST, Operator.IS_EMPTY, Operator.IS_NOT_EMPTY].map(operator => (
                <SelectInput.Option key={operator} value={operator} title={translateOperator(operator)}>
                    {translateOperator(operator)}
                </SelectInput.Option>
            ))}
        </SelectInput>
    );
};

export {AttributeSimpleSelectOperatorInput};
