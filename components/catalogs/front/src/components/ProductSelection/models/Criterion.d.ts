import {Operator} from './Operator';
import {FC} from 'react';
import {CriterionErrors} from './CriterionErrors';

export type CriterionModule<State> = {
    state: State;
    onChange: (state: State) => void;
    onRemove: () => void;
    errors: CriterionErrors;
};

export type CriterionState = {
    field: string;
    operator: Operator;
    value?: any;
};

export type Criterion<State extends CriterionState> = {
    component: FC<CriterionModule<State>>;
    factory: (state?: Partial<State>) => State;
};
