import {Operator} from './Operator';
import {FC} from 'react';

export type CriterionModule<State> = {
    state: State;
    onChange: (state: State) => void;
};

export type CriterionState = {
    field: string;
    operator: Operator;
};

export type Criterion<State extends CriterionState> = {
    id: string;
    module: FC<CriterionModule<State>>;
    state: State;
};
