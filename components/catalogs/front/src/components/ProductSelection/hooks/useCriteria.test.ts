jest.unmock('./useCriteria');

import {renderHook} from '@testing-library/react-hooks';
import {useCriteria} from './useCriteria';
import {Operator} from '../models/Operator';
import {useCatalogData} from '../../CatalogEdit/hooks/useCatalogData';

// const mockSetState = jest.fn();


// jest.mock('react', () => ({
//     ...jest.requireActual('react'),
//     useState: (initial: Criteria) => [initial, mockSetState],
// }));

test('it returns a criteria list and a setter', async done => {
    (useCatalogData as unknown as jest.MockedFunction<typeof useCatalogData>).mockImplementation(() => ({
        isLoading: false,
        isError: false,
        data: {
            product_selection_criteria: [
                {
                    field: 'foo',
                    operator: Operator.IS_EMPTY,
                },
            ],
        },
        error: null,
    }));

    const id = '123e4567-e89b-12d3-a456-426614174000';

    const {result, waitForNextUpdate, rerender} = renderHook(() => useCriteria(id));

    expect(result.current).toMatchObject([[], expect.any(Function)]);

    await waitForNextUpdate();

    expect(result.current).toMatchObject([[
        {
            id: expect.any(String),
            module: expect.any(Function),
            state: {
                field: 'foo',
                operator: Operator.IS_EMPTY,
            },
        }], expect.any(Function)]);

    done();
});
