jest.unmock('./useEditableCatalogCriteria');

import {mocked} from 'ts-jest';
import {Operator} from '../../ProductSelection/models/Operator';
import {renderHook} from '@testing-library/react-hooks';
import {useEditableCatalogCriteria} from './useEditableCatalogCriteria';
import {useCatalogCriteriaState} from './useCatalogCriteriaState';

test('it returns the state and a setter', () => {
    mocked(useCatalogCriteriaState).mockImplementation(() => ({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    }));

    const {result, rerender} = renderHook(() => useEditableCatalogCriteria('123e4567-e89b-12d3-a456-426614174000'));

    expect(result.current).toMatchObject([undefined, expect.any(Function)]);

    mocked(useCatalogCriteriaState).mockImplementation(() => ({
        isLoading: false,
        isError: false,
        data: [
            {
                field: 'enabled',
                operator: Operator.EQUALS,
                value: false,
            },
        ],
        error: null,
    }));

    rerender('123e4567-e89b-12d3-a456-426614174000');

    expect(result.current).toMatchObject([
        [
            {
                id: expect.any(String),
                module: expect.any(Function),
                state: [
                    {
                        field: 'enabled',
                        operator: Operator.EQUALS,
                        value: false,
                    },
                ],
            },
        ],
        expect.any(Function),
    ]);
});
