import React, {FC, useCallback} from 'react';
import {SelectInput} from 'akeneo-design-system';
import {Operator} from '../../models/Operator';
import {useOperatorTranslator} from '../../hooks/useOperatorTranslator';
import {AttributeMeasurementCriterionOperator, AttributeMeasurementCriterionState} from './types';

type Props = {
    state: AttributeMeasurementCriterionState;
    onChange: (state: AttributeMeasurementCriterionState) => void;
    isInvalid: boolean;
};

const AttributeMeasurementOperatorInput: FC<Props> = ({state, onChange, isInvalid}) => {
    const translateOperator = useOperatorTranslator();

    const handleChange = useCallback(
        (operator: string) => {
            onChange({
                ...state,
                operator: operator as AttributeMeasurementCriterionOperator,
                value: [Operator.IS_EMPTY, Operator.IS_NOT_EMPTY].includes(
                    operator as AttributeMeasurementCriterionOperator
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

export {AttributeMeasurementOperatorInput};
