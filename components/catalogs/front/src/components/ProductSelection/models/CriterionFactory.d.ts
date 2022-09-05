import {AnyCriterionState} from './Criterion';

export type CriterionFactory = {
    id: string;
    label: string;
    factory: () => AnyCriterionState;
};
