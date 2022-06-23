import {Criteria} from '../models/Criteria';
import {Dispatch, SetStateAction, useEffect, useState} from 'react';
import {useCatalogData} from '../../CatalogEdit/hooks/useCatalogData';
import {stateToCriterion} from '../criteria/stateToCriterion';

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
    }, [catalogData.data, catalogData.isLoading, catalogData.isError]);

    return [criteria, setCriteria];
};
