import React, {FC, useCallback} from 'react';
import {Helper, SelectInput} from 'akeneo-design-system';
import {Operator} from '../../models/Operator';
import {useOperatorTranslator} from '../../hooks/useOperatorTranslator';
import {FamilyCriterionOperator, FamilyCriterionState} from './types';

type Props = {
    state: FamilyCriterionState;
    onChange: (state: FamilyCriterionState) => void;
    error: string | undefined;
};

const FamilyOperatorInput: FC<Props> = ({state, onChange, error}) => {
    const translateOperator = useOperatorTranslator();

    const handleChange = useCallback(
        (operator: string) => {
            const containsFamilies = [Operator.IN_LIST, Operator.NOT_IN_LIST].includes(
                operator as FamilyCriterionOperator
            );

            onChange({
                ...state,
                operator: operator as FamilyCriterionOperator,
                value: containsFamilies ? state.value : [],
            });
        },
        [state, onChange]
    );

    return (
        <>
            <SelectInput
                emptyResultLabel=''
                openLabel=''
                value={state.operator}
                onChange={handleChange}
                clearable={false}
                invalid={error !== undefined}
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
            {error !== undefined && (
                <Helper inline level='error'>
                    {error}
                </Helper>
            )}
        </>
    );
};

export {FamilyOperatorInput};
