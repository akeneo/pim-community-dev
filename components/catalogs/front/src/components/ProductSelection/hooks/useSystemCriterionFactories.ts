import {useMemo} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import StatusCriterion from '../criteria/StatusCriterion';
import FamilyCriterion from '../criteria/FamilyCriterion';
import {CriterionFactory} from '../models/CriterionFactory';

export const useSystemCriterionFactories = (): CriterionFactory[] => {
    const translate = useTranslate();

    return useMemo(
        () => [
            {
                id: 'enabled',
                label: translate('akeneo_catalogs.product_selection.criteria.status.label'),
                factory: StatusCriterion.factory,
            },
            {
                id: 'family',
                label: translate('akeneo_catalogs.product_selection.criteria.family.label'),
                factory: FamilyCriterion.factory,
            },
        ],
        [translate]
    );
};
