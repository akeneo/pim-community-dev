import {Operator} from './Operator';
import {FC} from 'react';
import {CriterionErrors} from './CriterionErrors';
import {StatusCriterionState} from '../criteria/StatusCriterion';
import {FamilyCriterionState} from '../criteria/FamilyCriterion';

export type CriterionModule<State> = {
    state: State & any;
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

export type AnyCriterionState = StatusCriterionState | FamilyCriterionState;
export type AnyCriterion = Criterion<StatusCriterionState> | Criterion<FamilyCriterionState>;
