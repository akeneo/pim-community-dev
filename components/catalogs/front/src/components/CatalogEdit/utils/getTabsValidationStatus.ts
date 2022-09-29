import {CatalogFormErrors} from '../models/CatalogFormErrors';
import {Tabs} from '../components/TabBar';

type Status = {
    [key in Tabs]: boolean;
};

const settingsHasAnError = (errors: CatalogFormErrors): boolean => {
    return errors.find(error => error.propertyPath === '[enabled]') !== undefined;
};

const productSelectionCriteriaHasAnError = (errors: CatalogFormErrors): boolean => {
    return errors.find(error => error.propertyPath.startsWith('[product_selection_criteria]')) !== undefined;
};

const productValueFiltersHasAnError = (errors: CatalogFormErrors): boolean => {
    return errors.find(error => error.propertyPath.startsWith('[product_value_filters]')) !== undefined;
};

const productMappingHasAnError = (errors: CatalogFormErrors): boolean => {
    return errors.find(error => error.propertyPath.startsWith('[product_mapping]')) !== undefined;
};
export const getTabsValidationStatus = (errors: CatalogFormErrors): Status => {
    return {
        [Tabs.SETTINGS]: settingsHasAnError(errors),
        [Tabs.PRODUCT_SELECTION]: productSelectionCriteriaHasAnError(errors),
        [Tabs.PRODUCT_VALUE_FILTERS]: productValueFiltersHasAnError(errors),
        [Tabs.PRODUCT_MAPPING]: productMappingHasAnError(errors),
    };
};
