import {AnyCriterionState} from '../components/ProductSelection';

export type Catalog = {
    id: string;
    name: string;
    enabled: boolean;
    owner_username: string;
    product_selection_criteria: AnyCriterionState[];
};
