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
            <SelectInput.Option value={Operator.IN_LIST} title={translateOperator(Operator.IN_LIST)}>
                {translateOperator(Operator.IN_LIST)}
            </SelectInput.Option>
            <SelectInput.Option value={Operator.NOT_IN_LIST} title={translateOperator(Operator.NOT_IN_LIST)}>
                {translateOperator(Operator.NOT_IN_LIST)}
            </SelectInput.Option>
            <SelectInput.Option value={Operator.IN_CHILDREN_LIST} title={translateOperator(Operator.IN_CHILDREN_LIST)}>
                {translateOperator(Operator.IN_CHILDREN_LIST)}
            </SelectInput.Option>
            <SelectInput.Option
                value={Operator.NOT_IN_CHILDREN_LIST}
                title={translateOperator(Operator.NOT_IN_CHILDREN_LIST)}
            >
                {translateOperator(Operator.NOT_IN_CHILDREN_LIST)}
            </SelectInput.Option>
            <SelectInput.Option value={Operator.UNCLASSIFIED} title={translateOperator(Operator.UNCLASSIFIED)}>
                {translateOperator(Operator.UNCLASSIFIED)}
            </SelectInput.Option>
            <SelectInput.Option
                value={Operator.IN_LIST_OR_UNCLASSIFIED}
                title={translateOperator(Operator.IN_LIST_OR_UNCLASSIFIED)}
            >
                {translateOperator(Operator.IN_LIST_OR_UNCLASSIFIED)}
            </SelectInput.Option>
        </SelectInput>
    );
};

export {CategoryOperatorInput};
