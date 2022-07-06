import {useTranslate} from '@akeneo-pim-community/shared';
import {AnyCriterion, AnyCriterionState} from '../models/Criterion';
import StatusCriterion from '../criteria/StatusCriterion';
import {useCallback, useMemo} from 'react';

type Factory = {
    label: string;
    factory: () => AnyCriterionState;
};

type Result = {
    system: Factory[];
    getCriterionByField: (field: string) => Promise<AnyCriterion>;
};

export const useCriteriaRegistry = (): Result => {
    const translate = useTranslate();

    const system = useMemo(
        () => [
            {
                label: translate('akeneo_catalogs.product_selection.criteria.status.label'),
                factory: StatusCriterion.factory,
            },
        ],
        [translate]
    );

    const getCriterionByField = useCallback((field: string): Promise<AnyCriterion> => {
        switch (field) {
            case 'enabled':
                return Promise.resolve(StatusCriterion);
        }

        return Promise.reject();
    }, []);

    return {system, getCriterionByField};
};
