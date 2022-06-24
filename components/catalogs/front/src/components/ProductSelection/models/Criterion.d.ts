import {Operator} from './Operator';
import {FC} from 'react';

export type CriterionModule<State> = {
    state: State;
    onChange: (state: State) => void;
    onRemove: () => void;
};

export type CriterionState = {
    field: string;
    operator: Operator;
    value?: any;
};

export type Criterion<State extends CriterionState> = {
    id: string;
    module: FC<CriterionModule<State>>;
    state: State;
};
