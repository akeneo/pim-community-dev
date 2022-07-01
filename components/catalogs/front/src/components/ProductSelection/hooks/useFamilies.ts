import {useInfiniteQuery, UseInfiniteQueryResult} from 'react-query';

type Data = {
    data: {
        label: string;
        code: string;
    }[];
    pageNumber: number;
};
type Error = string | null;

type QueryKey = [string, {limit: number}];

// TODO search
export const useFamilies = (limit = 5): UseInfiniteQueryResult<Data, Error> => {
    const fetchFamilies = async (_: unknown, page = 1) => {
        const response = await fetch(
            `/rest/catalogs/families?page=${page}&limit=${limit}`,
            {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }
        );

        return {
            data: await response.json(),
            pageNumber: page,
        };
    };

    const getNextPageParam = (lastPage: Data) => lastPage.data.length > 0 ? lastPage.pageNumber++ : undefined;

    // TODO put search in query key
    return useInfiniteQuery<Data, Error, Data, QueryKey>(
        ['families', {limit: limit}],
        fetchFamilies,
        {
            getNextPageParam: getNextPageParam,
        }
    );
};
