import {useQuery} from 'react-query';
import {MeasurementUnit} from '../models/MeasurementUnit';
import {useUserContext} from '@akeneo-pim-community/shared';

type ResultError = Error | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: MeasurementUnit[] | undefined;
    error: ResultError;
};

export const useMeasurements = (measurementsFamilyCode: string | null): Result => {
    const locale = useUserContext().get('catalogLocale');

    return useQuery<MeasurementUnit[], ResultError, MeasurementUnit[]>(
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
