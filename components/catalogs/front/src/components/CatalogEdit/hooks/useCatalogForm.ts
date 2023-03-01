import {useCallback, useReducer, useState} from 'react';
import {useCatalog} from './useCatalog';
import {CatalogFormAction, CatalogFormActions, CatalogFormReducer} from '../reducers/CatalogFormReducer';
import {indexify} from '../utils/indexify';
import {useSaveCatalog} from './useSaveCatalog';
import {CatalogFormValues} from '../models/CatalogFormValues';
import {CatalogFormErrors} from '../models/CatalogFormErrors';
import {useCatalogErrors} from './useCatalogErrors';

export type CatalogForm = {
    values: CatalogFormValues;
    dispatch: Dispatch;
    errors: CatalogFormErrors;
};
type Dispatch = (action: CatalogFormAction) => void;
type Save = () => Promise<boolean>;
type IsDirty = boolean;
type Result = [CatalogForm | undefined, Save, IsDirty];

/* istanbul ignore next */
const loading: Result = [undefined, () => Promise.reject(), false];

export const useCatalogForm = (id: string): Result => {
    const catalog = useCatalog(id);
    const [initialized, setInitialized] = useState<boolean>(false);
    const [dirty, setDirty] = useState<boolean>(false);
    const [errors, setErrors] = useState<CatalogFormErrors>([]);
    const saveCatalog = useSaveCatalog();
    const {data: initialErrors} = useCatalogErrors(id);

    const [isFirstLoad, setIsFirstLoad] = useState<boolean>(true);
    if (isFirstLoad && initialErrors !== undefined) {
        setErrors(initialErrors);
        setIsFirstLoad(false);
    }

    const [values, dispatch] = useReducer(CatalogFormReducer, {
        enabled: false,
        product_selection_criteria: {},
        product_value_filters: {},
        product_mapping: {},
    });

    const save = async () => {
        const [success, errors] = await saveCatalog({
            id,
            values: {
                ...values,
                product_selection_criteria: Object.values(values.product_selection_criteria),
            },
        });

        if (success) {
            setDirty(false);
            setErrors([]);
        } else {
            setErrors(errors);
        }

        return success;
    };

    const isDirtyMiddleware: (dispatch: Dispatch) => Dispatch = useCallback(
        (dispatch: Dispatch): Dispatch =>
            action => {
                switch (action.type) {
                    case CatalogFormActions.INITIALIZE:
                        dispatch(action);
                        break;
                    case CatalogFormActions.SET_PRODUCT_SELECTION_CRITERIA:
                        setErrors(errors.filter(error => !error.propertyPath.startsWith('productSelectionCriteria')));
                        setDirty(true);
                        dispatch(action);
                        break;
                    case CatalogFormActions.SET_PRODUCT_MAPPING:
                        setErrors(errors.filter(error => !error.propertyPath.startsWith('productMapping')));
                        setDirty(true);
                        dispatch(action);
                        break;
                    default:
                        setDirty(true);
                        dispatch(action);
                        break;
                }
            },
        [setDirty, setErrors, errors]
    );

    if (catalog.isLoading) {
        return loading;
    }

    if (catalog.isError || undefined === catalog.data) {
        throw Error('Unable to initialize the catalog form with the backend data');
    }

    if (!initialized) {
        dispatch({
            type: CatalogFormActions.INITIALIZE,
            state: {
                enabled: catalog.data.enabled,
                product_selection_criteria: indexify(catalog.data.product_selection_criteria),
                product_value_filters: catalog.data.product_value_filters,
                product_mapping: catalog.data.product_mapping,
            },
        });

        setInitialized(true);

        return loading;
    }

    return [
        {
            values: values,
            dispatch: isDirtyMiddleware(dispatch),
            errors: errors,
        },
        save,
        dirty,
    ];
};
