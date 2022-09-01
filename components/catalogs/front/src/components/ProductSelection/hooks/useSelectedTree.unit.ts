jest.unmock('./useCategories');
jest.unmock('./useSelectedTree');
jest.unmock('./useCategoryTreeRoots');

import fetchMock from 'jest-fetch-mock';
import {act, renderHook} from '@testing-library/react-hooks';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import {useSelectedTree} from './useSelectedTree';

beforeEach(() => {
    fetchMock.mockResponse(req => {
        switch (req.url) {
            // useCategoryTreeRoots
            case '/rest/catalogs/categories?codes=&is_root=1&locale=en_US':
                return Promise.resolve(
                    JSON.stringify([
                        {
                            code: 'catA',
                            label: '[catA]',
                            isLeaf: false,
                        },
                        {
                            code: 'catB',
                            label: '[catB]',
                            isLeaf: false,
                        },
                    ])
                );
            default:
                throw Error(req.url);
        }
    });
});

test('it returns selected tree', async () => {
    const {result, waitForNextUpdate} = renderHook(() => useSelectedTree(), {
        wrapper: ReactQueryWrapper,
    });

    expect(result.current[0]).toBeNull();
    expect(result.current[1]).toStrictEqual(expect.any(Function));

    await waitForNextUpdate();

    expect(result.current[0]).toMatchObject({
        code: 'catA',
        label: '[catA]',
        isLeaf: false,
    });
});

test('it updates selected tree', () => {
    const {result} = renderHook(() => useSelectedTree(), {
        wrapper: ReactQueryWrapper,
    });
    expect(result.current[0]).toBeNull();

    act(() => {
        result.current[1]({
            code: 'master',
            label: '[master]',
            isLeaf: false,
        });
    });

    expect(result.current[0]).toMatchObject({
        code: 'master',
        label: '[master]',
        isLeaf: false,
    });
});

test('it throws if there is no tree to select', async () => {
    // mute the error in the output
    jest.spyOn(console, 'error');
    /* eslint-disable-next-line no-console */
    (console.error as jest.Mock).mockImplementation(() => null);

    fetchMock.mockResponse(JSON.stringify([]));

    const {result, waitForNextUpdate} = renderHook(() => useSelectedTree(), {
        wrapper: ReactQueryWrapper,
    });
    expect(result.current[0]).toBeNull();

    try {
        await waitForNextUpdate();
    } catch (error) {
        expect(error).toEqual(Error('No tree root found'));
    }
});
