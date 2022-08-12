import {Catalog} from '../models/Catalog';

type Values = Pick<Catalog, 'enabled' | 'product_selection_criteria'>;
type Errors = {
    propertyPath: string;
    message: string;
}[];
type ErrorResponse = {
    errors: Errors;
    message: string;
};

type Result = [true, never] | [false, Errors];

export const saveCatalog = async (id: string, values: Values): Promise<Result> => {
    const response = await fetch('/rest/catalogs/' + id, {
        method: 'PATCH',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(values),
    });

    if (!response.ok) {
        switch (response.status) {
            case 422:
                return [false, ((await response.json()) as ErrorResponse).errors] as Result;
            default:
                throw new Error(`${response.status} ${response.statusText}`);
        }
    }

    return [true, null] as Result;
};
