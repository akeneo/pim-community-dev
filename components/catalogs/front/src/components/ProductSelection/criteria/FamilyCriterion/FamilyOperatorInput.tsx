import React, {FC} from 'react';
import {SelectInput} from 'akeneo-design-system';
import {Operator} from '../../models/Operator';
import {useOperatorTranslator} from '../../hooks/useOperatorTranslator';
import {FamilyCriterionOperator, FamilyCriterionState} from './types';

type Props = {
    state: FamilyCriterionState;
    onChange: (state: FamilyCriterionState) => void;
};

const FamilyOperatorInput: FC<Props> = ({state, onChange}) => {
    const translateOperator = useOperatorTranslator();

    return (
        <SelectInput
            emptyResultLabel=''
            openLabel=''
            value={state.operator}
            onChange={v => onChange({...state, operator: v as FamilyCriterionOperator})}
            clearable={false}
            data-testid='operator'
        >
            <SelectInput.Option value={Operator.IS_EMPTY} title={translateOperator(Operator.IS_EMPTY)}>
                {translateOperator(Operator.IS_EMPTY)}
            </SelectInput.Option>
            <SelectInput.Option value={Operator.IS_NOT_EMPTY} title={translateOperator(Operator.IS_NOT_EMPTY)}>
                {translateOperator(Operator.IS_NOT_EMPTY)}
            </SelectInput.Option>
            <SelectInput.Option value={Operator.IN_LIST} title={translateOperator(Operator.IN_LIST)}>
                {translateOperator(Operator.IN_LIST)}
            </SelectInput.Option>
            <SelectInput.Option value={Operator.NOT_IN_LIST} title={translateOperator(Operator.NOT_IN_LIST)}>
                {translateOperator(Operator.NOT_IN_LIST)}
            </SelectInput.Option>
        </SelectInput>
    );
};

export {FamilyOperatorInput};
