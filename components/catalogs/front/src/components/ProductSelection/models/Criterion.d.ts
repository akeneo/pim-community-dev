import {Operator} from './Operator';
import {FC} from 'react';
import {CriterionErrors} from './CriterionErrors';
import {StatusCriterionState} from '../criteria/StatusCriterion';
import {FamilyCriterionState} from '../criteria/FamilyCriterion';
import {CompletenessCriterionState} from '../criteria/CompletenessCriterion';
import {CategoryCriterionState} from '../criteria/CategoryCriterion';
import {AttributeTextCriterionState} from '../criteria/AttributeTextCriterion';
import {AttributeSimpleSelectCriterionState} from '../criteria/AttributeSimpleSelectCriterion';
import {AttributeNumberCriterionState} from '../criteria/AttributeNumberCriterion';
import {AttributeMetricCriterionState} from '../criteria/AttributeMetricCriterion';
import {AttributeBooleanCriterionState} from '../criteria/AttributeBooleanCriterion';
import {AttributeIdentifierCriterionState} from '../criteria/AttributeIdentifierCriterion';

export type CriterionModule<State> = {
    /* eslint-disable-next-line @typescript-eslint/no-explicit-any */
    state: State & any;
    /* eslint-disable-next-line @typescript-eslint/no-explicit-any */
    onChange: (state: State & any) => void;
    onRemove: () => void;
    errors: CriterionErrors;
};

export type CriterionState = {
    field: string;
    operator: Operator;
    /* eslint-disable-next-line @typescript-eslint/no-explicit-any */
    value?: any;
    locale?: string;
    scope?: string;
};

export type Criterion<State extends CriterionState> = {
    component: FC<CriterionModule<State>>;
    factory: (state?: Partial<State>) => State;
};

export type AnyCriterionState =
    | StatusCriterionState
    | FamilyCriterionState
    | CompletenessCriterionState
    | CategoryCriterionState
    | AttributeIdentifierCriterionState
    | AttributeTextCriterionState
    | AttributeSimpleSelectCriterionState
    | AttributeNumberCriterionState
    | AttributeMetricCriterionState
    | AttributeBooleanCriterionState;

export type AnyAttributeCriterion =
    | Criterion<AttributeIdentifierCriterionState>
    | Criterion<AttributeTextCriterionState>
    | Criterion<AttributeSimpleSelectCriterionState>
    | Criterion<AttributeNumberCriterionState>
    | Criterion<AttributeMetricCriterionState>
    | Criterion<AttributeBooleanCriterionState>;

export type AnyCriterion =
    | Criterion<StatusCriterionState>
    | Criterion<FamilyCriterionState>
    | Criterion<CompletenessCriterionState>
    | Criterion<CategoryCriterionState>
    | AnyAttributeCriterion;
