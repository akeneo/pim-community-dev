jest.unmock('./useAttributeOptionsByCodes');
jest.unmock('./useInfiniteAttributeOptions');

import {mockFetchResponses} from '../../../../tests/mockFetchResponses';
import {renderHook} from '@testing-library/react-hooks';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import {useAttributeOptionsByCodes} from './useAttributeOptionsByCodes';

const OPTION_XS = {code: 'xs', label: 'XS'};
const OPTION_S = {code: 's', label: 'S'};

test('it returns attribute options', async () => {
    mockFetchResponses([
        {
            url: '/rest/catalogs/attributes/clothing_size/options?locale=en_US&codes=xs%2Cs%2Cl&search=&page=1&limit=20',
            json: [OPTION_XS, OPTION_S],
        },
        {
            url: '/rest/catalogs/attributes/clothing_size/options?locale=en_US&codes=l&search=&page=1&limit=20',
            json: [],
        },
    ]);

    const {result, waitForValueToChange} = renderHook(
        () => useAttributeOptionsByCodes('clothing_size', ['xs', 's', 'l']),
        {
            wrapper: ReactQueryWrapper,
        }
    );

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
            OPTION_XS,
            OPTION_S,
            {
                code: 'l',
                label: '[l]', // removed options are wrapped with brackets
            },
        ],
        error: null,
    });
});
