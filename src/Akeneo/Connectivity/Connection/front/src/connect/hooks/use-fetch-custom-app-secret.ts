import {useQuery} from 'react-query';

type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: string | undefined;
    error: Error;
};

export const useFetchCustomAppSecret = (customAppId: string): Result => {
    return useQuery<string, Error, string>(['id', customAppId], async () => {
        const response = await fetch(`/rest/marketplace/custom-apps/${customAppId}/secret`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        return await response.json();
    });
};
