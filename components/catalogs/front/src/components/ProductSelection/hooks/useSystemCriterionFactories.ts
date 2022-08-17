import {useMemo} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {CriterionFactory} from '../models/CriterionFactory';
import StatusCriterion from '../criteria/StatusCriterion';
import FamilyCriterion from '../criteria/FamilyCriterion';
import CompletenessCriterion from '../criteria/CompletenessCriterion';

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
            {
                id: 'completeness',
                label: translate('akeneo_catalogs.product_selection.criteria.completeness.label'),
                factory: CompletenessCriterion.factory,
            },
        ],
        [translate]
    );
};
