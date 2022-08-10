import {AnyCriterionState} from './Criterion';

export type CriterionFactory = {
    label: string;
    factory: () => AnyCriterionState;
};
