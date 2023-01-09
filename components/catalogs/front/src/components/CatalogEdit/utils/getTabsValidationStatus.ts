import {CatalogFormErrors} from '../models/CatalogFormErrors';
import {Tabs} from '../components/TabBar';

type Status = {
    [key in Tabs]: boolean;
};

const productSelectionCriteriaHasAnError = (errors: CatalogFormErrors): boolean => {
    return errors.find(error => error.propertyPath.startsWith('productSelectionCriteria')) !== undefined;
};

const productValueFiltersHasAnError = (errors: CatalogFormErrors): boolean => {
    return errors.find(error => error.propertyPath.startsWith('productValueFilters')) !== undefined;
};

const productMappingHasAnError = (errors: CatalogFormErrors): boolean => {
    return errors.find(error => error.propertyPath.startsWith('productMapping')) !== undefined;
};
export const getTabsValidationStatus = (errors: CatalogFormErrors): Status => {
    return {
        [Tabs.PRODUCT_SELECTION]: productSelectionCriteriaHasAnError(errors),
        [Tabs.PRODUCT_VALUE_FILTERS]: productValueFiltersHasAnError(errors),
        [Tabs.PRODUCT_MAPPING]: productMappingHasAnError(errors),
    };
};
