import React, {FC, useCallback} from 'react';
import {SelectInput} from 'akeneo-design-system';
import {Operator} from '../../models/Operator';
import {useOperatorTranslator} from '../../hooks/useOperatorTranslator';
import {AttributeYesNoCriterionOperator, AttributeYesNoCriterionState} from './types';

type Props = {
    state: AttributeYesNoCriterionState;
    onChange: (state: AttributeYesNoCriterionState) => void;
    isInvalid: boolean;
};

const AttributeYesNoOperatorInput: FC<Props> = ({state, onChange, isInvalid}) => {
    const translateOperator = useOperatorTranslator();

    const handleChange = useCallback(
        (operator: string) => {
            onChange({
                ...state,
                operator: operator as AttributeYesNoCriterionOperator,
                value: [Operator.IS_EMPTY, Operator.IS_NOT_EMPTY].includes(operator as AttributeYesNoCriterionOperator)
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

export {AttributeYesNoOperatorInput};
