import React, {FC, useCallback} from 'react';
import {SelectInput} from 'akeneo-design-system';
import {Operator} from '../../models/Operator';
import {useOperatorTranslator} from '../../hooks/useOperatorTranslator';
import {CategoryCriterionOperator, CategoryCriterionState} from './types';

type Props = {
    state: CategoryCriterionState;
    onChange: (state: CategoryCriterionState) => void;
    isInvalid: boolean;
};

const CategoryOperatorInput: FC<Props> = ({state, onChange, isInvalid}) => {
    const translateOperator = useOperatorTranslator();

    const handleChange = useCallback(
        (operator: string) => {
            onChange({
                ...state,
                operator: operator as CategoryCriterionOperator,
                value: Operator.UNCLASSIFIED === operator ? [] : state.value,
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
                Operator.IN_LIST,
                Operator.NOT_IN_LIST,
                Operator.IN_CHILDREN_LIST,
                Operator.NOT_IN_CHILDREN_LIST,
                Operator.UNCLASSIFIED,
                Operator.IN_LIST_OR_UNCLASSIFIED,
            ].map(operator => (
                <SelectInput.Option key={operator} value={operator} title={translateOperator(operator)}>
                    {translateOperator(operator)}
                </SelectInput.Option>
            ))}
        </SelectInput>
    );
};

export {CategoryOperatorInput};
