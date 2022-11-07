import {useCallback, useEffect, useReducer, useRef, useState} from 'react';
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
                    default:
                        setDirty(true);
                        dispatch(action);
                        break;
                }
            },
        [setDirty]
    );

    const prevValuesRef = useRef<CatalogFormValues>(values);

    useEffect(() => {
        const productSelectionCriteriaPreviousKeys = Object.keys(prevValuesRef.current?.product_selection_criteria);
        const productSelectionCriteriaCurrentKeys = Object.keys(values?.product_selection_criteria);

        if (productSelectionCriteriaPreviousKeys.length > productSelectionCriteriaCurrentKeys.length) {
            productSelectionCriteriaPreviousKeys.forEach((value, index) => {
                if (!productSelectionCriteriaCurrentKeys.includes(value)) {
                    setErrors(errors.filter(error => {
                        return !error.propertyPath.startsWith(`[product_selection_criteria][${index}]`);
                    }));
                    return false;
                }
            });
        }
        prevValuesRef.current = values;
    }, [values, isFirstLoad]);

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
