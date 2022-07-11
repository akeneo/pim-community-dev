import {AnyCriterionState} from './Criteria';

export type CriterionErrors = {
    [key in keyof AnyCriterionState]: string | null;
};
