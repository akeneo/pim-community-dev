import React, {FC} from 'react';
import {AttributeDateCriterionState} from './types';
import {CriterionField} from '../../components/CriterionFields';
import DateInput from './DateInput';

type Props = {
    state: AttributeDateCriterionState;
    onChange: (state: AttributeDateCriterionState) => void;
    isInvalid: boolean;
};

const value = (state: AttributeDateCriterionState, index: number): string => {
    if (null === state.value || !Array.isArray(state.value)) {
        return '';
    }

    return state.value[index] ?? '';
};

const AttributeDateValueMultiInput: FC<Props> = ({state, onChange, isInvalid}) => {
    return (
        <>
            <CriterionField width={140}>
                <DateInput
                    onChange={v => onChange({...state, value: [v, value(state, 1)]})}
                    value={value(state, 0)}
                    invalid={isInvalid}
                    data-testid='value-from'
                    required
                />
            </CriterionField>
            <CriterionField width={140}>
                <DateInput
                    onChange={v => onChange({...state, value: [value(state, 0), v]})}
                    value={value(state, 1)}
                    invalid={isInvalid}
                    data-testid='value-to'
                    required
                />
            </CriterionField>
        </>
    );
};

export {AttributeDateValueMultiInput};
