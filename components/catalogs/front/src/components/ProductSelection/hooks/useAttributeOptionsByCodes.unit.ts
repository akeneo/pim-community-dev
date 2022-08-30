jest.unmock('./useAttributeOptionsByCodes');
jest.unmock('./useInfiniteAttributeOptions');

import {mockFetchResponses} from '../../../../tests/mockFetchResponses';
import {renderHook} from '@testing-library/react-hooks';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import {useAttributeOptionsByCodes} from './useAttributeOptionsByCodes';

test('it returns attribute options', async () => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/attributes/clothing_size/options?locale=en_US&codes=xs%2Cs&search=&page=1&limit=20',
            json: [
                {
                    code: 'xs',
                    label: 'XS',
                },
                {
                    code: 's',
                    label: 'S',
                },
            ],
        },
    ]);

    const {result, waitForValueToChange} = renderHook(() => useAttributeOptionsByCodes('clothing_size', ['xs', 's']), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current).toMatchObject({
        isLoading: true,
        isError: false,
        data: [],
        error: null,
    });

    await waitForValueToChange(() => result.current.isLoading);

    expect(result.current).toMatchObject({
        isLoading: false,
        isError: false,
        data: [
            {
                code: 'xs',
                label: 'XS',
            },
            {
                code: 's',
                label: 'S',
            },
        ],
        error: null,
    });
});
