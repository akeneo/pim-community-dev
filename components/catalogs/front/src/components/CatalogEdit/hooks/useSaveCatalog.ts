import {useCallback} from 'react';
import {useMutation, useQueryClient} from 'react-query';
import {AnyCriterionState} from '../../ProductSelection';
import {CatalogFormErrors} from '../models/CatalogFormErrors';

type RequestPayload = {
    enabled: boolean;
    product_selection_criteria: AnyCriterionState[];
};
type ErrorPayload = {
    errors: CatalogFormErrors;
    message: string;
};

type Result = [true, never] | [false, CatalogFormErrors];
type Args = {id: string; values: RequestPayload};
export type SaveCatalog = (args: Args) => Promise<Result>;

export const useSaveCatalog = (): SaveCatalog => {
    const queryClient = useQueryClient();

    const request = useCallback(async ({id, values}: Args) => {
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
                    return [false, ((await response.json()) as ErrorPayload).errors] as Result;
                default:
                    throw new Error(`${response.status} ${response.statusText}`);
            }
        }

        return [true, null] as Result;
    }, []);

    const mutation = useMutation(request, {
        onSuccess: () => {
            queryClient.invalidateQueries('catalog');
        },
    });

    return mutation.mutateAsync;
};
