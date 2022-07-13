import {Operator} from './Operator';
import {FC} from 'react';
import {CriterionErrors} from './CriterionErrors';
import {StatusCriterionState} from '../criteria/StatusCriterion';
import {FamilyCriterionState} from '../criteria/FamilyCriterion';
import {CompletenessCriterionState} from '../criteria/CompletenessCriterion';

export type CriterionModule<State> = {
    /* eslint-disable-next-line @typescript-eslint/no-explicit-any */
    state: State & any;
    onChange: (state: State) => void;
    onRemove: () => void;
    errors: CriterionErrors;
};

export type CriterionState = {
    field: string;
    operator: Operator;
    /* eslint-disable-next-line @typescript-eslint/no-explicit-any */
    value?: any;
};

export type Criterion<State extends CriterionState> = {
    component: FC<CriterionModule<State>>;
    factory: (state?: Partial<State>) => State;
};

export type AnyCriterionState = StatusCriterionState | FamilyCriterionState | CompletenessCriterionState;
export type AnyCriterion =
    | Criterion<StatusCriterionState>
    | Criterion<FamilyCriterionState>
    | Criterion<CompletenessCriterionState>;
