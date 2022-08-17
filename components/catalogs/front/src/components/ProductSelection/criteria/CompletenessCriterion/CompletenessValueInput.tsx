import React, {FC} from 'react';
import {getColor, TextInput} from 'akeneo-design-system';
import {CompletenessCriterionState} from './types';
import styled from 'styled-components';

const PercentageWrapper = styled.div`
    position: relative;
    &:after {
        position: absolute;
        top: 12px;
        right: 12px;
        content: '%';
        color: ${getColor('grey', 100)};
    }
`;
type Props = {
    state: CompletenessCriterionState;
    onChange: (state: CompletenessCriterionState) => void;
    isInvalid: boolean;
};

const CompletenessValueInput: FC<Props> = ({state, onChange, isInvalid}) => {
    return (
        <PercentageWrapper>
            <TextInput
                onChange={v => onChange({...state, value: parseInt(v) || 0})}
                value={state.value.toString()}
                invalid={isInvalid}
                data-testid='value'
            />
        </PercentageWrapper>
    );
};

export {CompletenessValueInput};
