import {AnyCriterionState} from './Criterion';

export type CriterionErrors = {
    [key in keyof AnyCriterionState]?: string;
};
