import React, {FC, useCallback} from 'react';
import {SelectInput} from 'akeneo-design-system';
import {Operator} from '../../models/Operator';
import {useOperatorTranslator} from '../../hooks/useOperatorTranslator';
import {AttributeTextCriterionOperator, AttributeTextCriterionState} from './types';

type Props = {
    state: AttributeTextCriterionState;
    onChange: (state: AttributeTextCriterionState) => void;
    isInvalid: boolean;
};

const AttributeTextOperatorInput: FC<Props> = ({state, onChange, isInvalid}) => {
    const translateOperator = useOperatorTranslator();

    const handleChange = useCallback(
        (operator: string) => {
            onChange({
                ...state,
                operator: operator as AttributeTextCriterionOperator,
                value: [Operator.IS_EMPTY, Operator.IS_NOT_EMPTY].includes(operator as AttributeTextCriterionOperator)
                    ? ''
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
            {[
                Operator.EQUALS,
                Operator.NOT_EQUAL,
                Operator.CONTAINS,
                Operator.DOES_NOT_CONTAIN,
                Operator.STARTS_WITH,
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

export {AttributeTextOperatorInput};
