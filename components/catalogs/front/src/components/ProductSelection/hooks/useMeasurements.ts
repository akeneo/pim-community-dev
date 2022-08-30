import {useQuery} from 'react-query';
import {Measurement} from '../models/Measurement';
import {useUserContext} from '@akeneo-pim-community/shared';

type ResultError = Error | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: Measurement[] | undefined;
    error: ResultError;
};

export const useMeasurements = (measurementsFamilyCode: string | null): Result => {
    const locale = useUserContext().get('catalogLocale');

    return useQuery<Measurement[], ResultError, Measurement[]>(
        ['measurements', measurementsFamilyCode, {locale: locale}],
        async () => {
            if (null === measurementsFamilyCode) {
                return [];
            }
            const queryParameters = new URLSearchParams({
                locale: locale,
            }).toString();

            const response = await fetch(
                '/rest/catalogs/measurement-families/' + measurementsFamilyCode + '/units?' + queryParameters,
                {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                }
            );

            return await response.json();
        }
    );
};
