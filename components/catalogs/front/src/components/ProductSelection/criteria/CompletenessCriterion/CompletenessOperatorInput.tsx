import React, {FC, useCallback} from 'react';
import {Helper, SelectInput} from 'akeneo-design-system';
import {Operator} from '../../models/Operator';
import {useOperatorTranslator} from '../../hooks/useOperatorTranslator';
import {CompletenessCriterionOperator, CompletenessCriterionState} from './types';

type Props = {
    state: CompletenessCriterionState;
    onChange: (state: CompletenessCriterionState) => void;
    error: string | undefined;
};

const CompletenessOperatorInput: FC<Props> = ({state, onChange, error}) => {
    const translateOperator = useOperatorTranslator();

    const handleChange = useCallback(
        (operator: string) => {
            onChange({
                ...state,
                operator: operator as CompletenessCriterionOperator,
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
                data-testid='operator'
            >
                <SelectInput.Option value={Operator.EQUALS} title={translateOperator(Operator.EQUALS)}>
                    {translateOperator(Operator.EQUALS)}
                </SelectInput.Option>
                <SelectInput.Option value={Operator.NOT_EQUAL} title={translateOperator(Operator.NOT_EQUAL)}>
                    {translateOperator(Operator.NOT_EQUAL)}
                </SelectInput.Option>
                <SelectInput.Option value={Operator.LOWER_THAN} title={translateOperator(Operator.LOWER_THAN)}>
                    {translateOperator(Operator.LOWER_THAN)}
                </SelectInput.Option>
                <SelectInput.Option value={Operator.GREATER_THAN} title={translateOperator(Operator.GREATER_THAN)}>
                    {translateOperator(Operator.GREATER_THAN)}
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

export {CompletenessOperatorInput};
