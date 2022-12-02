import {AnyCriterionState} from './Criterion';

export type CriterionFactory = {
    id: string;
    label: string;
    group_code: string;
    group_label: string;
    factory: () => AnyCriterionState;
};
