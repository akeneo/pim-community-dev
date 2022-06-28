import {AnyCriterionState} from '../../ProductSelection';

export type CatalogFormValues = {
    enabled: boolean;
    product_selection_criteria: {
        [key: string]: AnyCriterionState;
    };
};
