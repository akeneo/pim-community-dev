import React, {FC} from 'react';
import {AttributeDateCriterionState} from './types';
import DateInput from './DateInput';

type Props = {
    state: AttributeDateCriterionState;
    onChange: (state: AttributeDateCriterionState) => void;
    isInvalid: boolean;
};

const AttributeDateValueSingleInput: FC<Props> = ({state, onChange, isInvalid}) => {
    return (
        <DateInput
            onChange={v => onChange({...state, value: v})}
            value={state.value?.toString() ?? ''}
            invalid={isInvalid}
            data-testid='value'
            required
        />
    );
};

export {AttributeDateValueSingleInput};
