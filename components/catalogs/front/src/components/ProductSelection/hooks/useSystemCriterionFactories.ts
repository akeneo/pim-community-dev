import {useMemo} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {CriterionFactory} from '../models/CriterionFactory';
import StatusCriterion from '../criteria/StatusCriterion';
import FamilyCriterion from '../criteria/FamilyCriterion';
import CompletenessCriterion from '../criteria/CompletenessCriterion';
import CategoryCriterion from '../criteria/CategoryCriterion';

export const useSystemCriterionFactories = (): CriterionFactory[] => {
    const translate = useTranslate();

    return useMemo(
        () => [
            {
                id: 'enabled',
                label: translate('akeneo_catalogs.product_selection.criteria.status.label'),
                group_code: 'system',
                group_label: translate('akeneo_catalogs.product_selection.add_criteria.section_system'),
                factory: StatusCriterion.factory,
            },
            {
                id: 'family',
                label: translate('akeneo_catalogs.product_selection.criteria.family.label'),
                group_code: 'system',
                group_label: translate('akeneo_catalogs.product_selection.add_criteria.section_system'),
                factory: FamilyCriterion.factory,
            },
            {
                id: 'completeness',
                label: translate('akeneo_catalogs.product_selection.criteria.completeness.label'),
                group_code: 'system',
                group_label: translate('akeneo_catalogs.product_selection.add_criteria.section_system'),
                factory: CompletenessCriterion.factory,
            },
            {
                id: 'categories',
                label: translate('akeneo_catalogs.product_selection.criteria.category.label'),
                group_code: 'system',
                group_label: translate('akeneo_catalogs.product_selection.add_criteria.section_system'),
                factory: CategoryCriterion.factory,
            },
        ],
        [translate]
    );
};
