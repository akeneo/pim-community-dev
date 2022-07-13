import React, {FC} from 'react';
import {Helper, TextInput} from 'akeneo-design-system';
import {CompletenessCriterionState} from './types';

type Props = {
    state: CompletenessCriterionState;
    onChange: (state: CompletenessCriterionState) => void;
    error: string | undefined;
};

const CompletenessValueInput: FC<Props> = ({state, onChange, error}) => {
    return (
        <>
            <TextInput
                onChange={v => onChange({...state, value: parseInt(v) || 0})}
                value={state.value.toString()}
                data-testid='value'
            />
            {error !== undefined && (
                <Helper inline level='error'>
                    {error}
                </Helper>
            )}
        </>
    );
};

export {CompletenessValueInput};
