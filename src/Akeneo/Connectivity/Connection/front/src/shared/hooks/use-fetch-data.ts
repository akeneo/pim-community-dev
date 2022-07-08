import {useCallback, useEffect, useState} from 'react';
import {useRoute} from '../../shared/router';

interface ParametersType {
    [param: string]: string;
}

export const useFetchData = <FetchedDataType>(
    route: string,
    parameters?: ParametersType
): {isLoading: boolean; data: FetchedDataType | undefined} => {
    const url = useRoute(route, parameters);

    const fetchDataCallback = useCallback(async () => {
        const response = await fetch(url, {
            method: 'GET',
            headers: [['X-Requested-With', 'XMLHttpRequest']],
        });
        if (!response.ok) {
            return Promise.reject(`${response.status} ${response.statusText}`);
        }

        return response.json();
    }, [url]);

    const [fetchedData, setFetchedData] = useState<FetchedDataType | undefined>(undefined);
    const [isLoading, setLoading] = useState<boolean>(true);

    useEffect(() => {
        fetchDataCallback()
            .then(setFetchedData)
            .catch(() => setFetchedData(undefined))
            .finally(() => setLoading(false));
    }, []);

    return {isLoading, data: fetchedData};
};
