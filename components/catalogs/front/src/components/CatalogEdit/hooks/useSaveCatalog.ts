import {useMutation, useQueryClient} from 'react-query';
import {AnyCriterionState} from '../../ProductSelection';
import {CatalogFormErrors} from '../models/CatalogFormErrors';
import {saveCatalog} from '../../../api/saveCatalog';

type RequestPayload = {
    enabled: boolean;
    product_selection_criteria: AnyCriterionState[];
};

type Result = [true, never] | [false, CatalogFormErrors];
type Args = {id: string; values: RequestPayload};
export type SaveCatalog = (args: Args) => Promise<Result>;

export const useSaveCatalog = (): SaveCatalog => {
    const queryClient = useQueryClient();

    const saver = async ({id, values}: Args) => await saveCatalog(id, values);

    const mutation = useMutation(saver, {
        onSuccess: () => {
            queryClient.invalidateQueries('catalog');
        },
    });

    return mutation.mutateAsync;
};
