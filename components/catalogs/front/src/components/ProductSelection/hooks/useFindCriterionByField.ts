import {useCallback} from 'react';
import {AnyCriterion} from '../models/Criterion';
import {useQueryClient} from 'react-query';
import {Attribute} from '../../../models/Attribute';
import {useFindAttributeCriterionByType} from './useFindAttributeCriterionByType';
import StatusCriterion from '../criteria/StatusCriterion';
import FamilyCriterion from '../criteria/FamilyCriterion';
import CompletenessCriterion from '../criteria/CompletenessCriterion';
import CategoryCriterion from '../criteria/CategoryCriterion';

type Return = (field: string) => Promise<AnyCriterion>;

export const useFindCriterionByField = (): Return => {
    const client = useQueryClient();
    const findAttributeCriterionByType = useFindAttributeCriterionByType();

    return useCallback(
        async (field: string): Promise<AnyCriterion> => {
            switch (field) {
                case 'enabled':
                    return Promise.resolve(StatusCriterion);
                case 'family':
                    return Promise.resolve(FamilyCriterion);
                case 'completeness':
                    return Promise.resolve(CompletenessCriterion);
                case 'categories':
                    return Promise.resolve(CategoryCriterion);
            }

            try {
                const attribute: Attribute = await client.fetchQuery(
                    ['attribute', field],
                    async () => {
                        const response = await fetch(`/rest/catalogs/attributes/${field}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        return await response.json();
                    },
                    {
                        retry: false,
                    }
                );

                return Promise.resolve(findAttributeCriterionByType(attribute.type));
            } catch (e) {
                return Promise.reject();
            }
        },
        [client, findAttributeCriterionByType]
    );
};
