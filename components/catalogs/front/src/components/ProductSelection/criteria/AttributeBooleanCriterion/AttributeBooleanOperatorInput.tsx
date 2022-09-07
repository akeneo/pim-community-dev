import React, {FC, useCallback} from 'react';
import {SelectInput} from 'akeneo-design-system';
import {Operator} from '../../models/Operator';
import {useOperatorTranslator} from '../../hooks/useOperatorTranslator';
import {AttributeBooleanCriterionOperator, AttributeBooleanCriterionState} from './types';

type Props = {
    state: AttributeBooleanCriterionState;
    onChange: (state: AttributeBooleanCriterionState) => void;
    isInvalid: boolean;
};

const AttributeBooleanOperatorInput: FC<Props> = ({state, onChange, isInvalid}) => {
    const translateOperator = useOperatorTranslator();

    const handleChange = useCallback(
        (operator: string) => {
            onChange({
                ...state,
                operator: operator as AttributeBooleanCriterionOperator,
                value: [Operator.IS_EMPTY, Operator.IS_NOT_EMPTY].includes(
                    operator as AttributeBooleanCriterionOperator
                )
                    ? null
                    : state.value,
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
            {[Operator.EQUALS, Operator.NOT_EQUAL, Operator.IS_EMPTY, Operator.IS_NOT_EMPTY].map(operator => (
                <SelectInput.Option key={operator} value={operator} title={translateOperator(operator)}>
                    {translateOperator(operator)}
                </SelectInput.Option>
            ))}
        </SelectInput>
    );
};

export {AttributeBooleanOperatorInput};
