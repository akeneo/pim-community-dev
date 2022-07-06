import {ProductSelectionValues} from './ProductSelectionValues';
import {CriterionErrors} from './CriterionErrors';

export type ProductSelectionErrors = {
    [key in keyof ProductSelectionValues]?: CriterionErrors;
};
