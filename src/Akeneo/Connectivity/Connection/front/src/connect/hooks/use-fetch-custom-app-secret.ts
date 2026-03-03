import {useQuery} from 'react-query';

type Error = string | null;
type Result = {
    isLoading: boolean;
    isError: boolean;
    data: string | undefined;
    error: Error;
};

export const useFetchCustomAppSecret = (customAppId: string): Result => {
    return useQuery<string, Error, string>(['custom_app_secret', customAppId], async () => {
        const response = await fetch(`/rest/custom-apps/${customAppId}/secret`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        return await response.json();
    });
};
