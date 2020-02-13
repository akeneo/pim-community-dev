import {fetchResult} from '@src/shared/fetch-result/fetch-result';
import {ok, err} from '@src/shared/fetch-result/result';

describe('fetchResult', () => {
    beforeEach(() => {
        fetchMock.resetMocks();
    });

    it('should handle successful response', async () => {
        fetchMock.mockResponseOnce(JSON.stringify({data: 'My test data.'}), {status: 200});

        const result = await fetchResult('my_request_uri');

        expect(fetchMock).toBeCalledWith('my_request_uri', {
            credentials: 'include',
        });
        expect(result).toStrictEqual(ok({data: 'My test data.'}));
    });

    it('should handle successful response with no content (204)', async () => {
        fetchMock.mockResponseOnce('', {status: 204});

        const result = await fetchResult('my_request_uri');

        expect(result).toStrictEqual(ok(undefined));
    });

    it('should handle client error (400)', async () => {
        fetchMock.mockResponseOnce(JSON.stringify({data: 'My test data.'}), {status: 400});

        const result = await fetchResult('my_request_uri');

        expect(result).toStrictEqual(err({data: 'My test data.'}));
    });

    it('should throw for server error (500)', async () => {
        const error = new Error();
        fetchMock.mockRejectOnce(error);

        try {
            await fetchResult('my_request_uri');
        } catch (e) {
            expect(e).toBe(error);
        }
    });
});
