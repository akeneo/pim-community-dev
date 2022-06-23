import {Dispatch, SetStateAction, useEffect, useState} from 'react';
import {Criteria} from '../../ProductSelection/models/Criteria';
import {useCatalogData} from './useCatalogData';
import {stateToCriterion} from '../../ProductSelection/criteria/stateToCriterion';

export const useCriteria = (id: string): [Criteria, Dispatch<SetStateAction<Criteria>>] => {
    const catalogData = useCatalogData(id);
    const [criteria, setCriteria] = useState<Criteria>([]);

    useEffect(() => {
        if (catalogData.isLoading) {
            return;
        }

        if (catalogData.isError || undefined === catalogData.data) {
            return;
        }

        const mappedCriteria = catalogData.data.product_selection_criteria.map(state => stateToCriterion(state));

        setCriteria(mappedCriteria);
    }, [catalogData.isLoading, catalogData.isError]);

    return [criteria, setCriteria];
};
