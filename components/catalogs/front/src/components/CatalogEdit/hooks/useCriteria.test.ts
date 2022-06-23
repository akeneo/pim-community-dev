jest.unmock('./useCriteria');
jest.unmock('../../ProductSelection/criteria/stateToCriterion');
jest.unmock('../../ProductSelection/criteria/StatusCriterion');

import {renderHook} from '@testing-library/react-hooks';

import {useCriteria} from './useCriteria';
import {useCatalogData} from './useCatalogData';
import {CriteriaState} from '../../ProductSelection/models/Criteria';


test('it returns a criteria list and a setter', () => {
    const id = '123e4567-e89b-12d3-a456-426614174000';
    const dummyState = {
        field: 'enabled',
        operator: '=',
        value: false,
    };

    const useCatalogsDataMock = useCatalogData as unknown as jest.MockedFunction<typeof useCatalogData>;

    useCatalogsDataMock.mockImplementationOnce((id) => ({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    }));

    useCatalogsDataMock.mockImplementation((id) => ({
        isLoading: false,
        isError: false,
        data: {
            product_selection_criteria: [dummyState] as CriteriaState,
        },
        error: null,
    }));

    const {result, rerender} = renderHook(() => useCriteria(id));

    expect(result.current).toMatchObject([[], expect.any(Function)]);

    rerender();

    expect(result.current).toMatchObject([
        [
            {
                id: expect.any(String),
                module: expect.any(Function),
                state: dummyState,
            }
        ],
        expect.any(Function)
    ]);

});
