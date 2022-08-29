import fetchMock from 'jest-fetch-mock';

type Response = {
    url: string;
    /* eslint-disable-next-line @typescript-eslint/no-explicit-any */
    json: any;
};

export const mockFetchResponses = (responses: Response[]): void => {
    fetchMock.doMock(request => {
        const response = responses.find(response => response.url === request.url);

        if (response) {
            return Promise.resolve(JSON.stringify(response.json));
        }

        // eslint-disable-next-line no-console
        console.warn(request.url);

        return Promise.reject();
    });
};
