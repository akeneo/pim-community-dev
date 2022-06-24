jest.unmock('./useEditableCatalogCriteria');

import {mocked} from 'ts-jest/utils';
import {Operator} from '../../ProductSelection/models/Operator';
import {stateToCriterion} from '../../ProductSelection/criteria/stateToCriterion';
import {StatusCriterionOperator} from '../../ProductSelection/criteria/StatusCriterion/types';
import {renderHook} from '@testing-library/react-hooks';
import {useEditableCatalogCriteria} from './useEditableCatalogCriteria';
import {useCatalogCriteriaState} from './useCatalogCriteriaState';

test('it returns the state and a setter', () => {
    mocked(stateToCriterion).mockImplementation(state => ({
        id: 'abc',
        module: () => null,
        state: {
            field: 'enabled',
            operator: state.operator as StatusCriterionOperator,
            value: state.value,
        },
    }));
    mocked(useCatalogCriteriaState).mockImplementation(() => ({
        isLoading: true,
        isError: false,
        data: undefined,
        error: null,
    }));

    const {result, rerender} = renderHook(() => useEditableCatalogCriteria('123e4567-e89b-12d3-a456-426614174000'));

    expect(result.current).toMatchObject([undefined, undefined]);

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
                state: {
                    field: 'enabled',
                    operator: Operator.EQUALS,
                    value: false,
                },
            },
        ],
        expect.any(Function),
    ]);
});

test('it throws if the API call failed', () => {
    mocked(useCatalogCriteriaState).mockImplementation(() => ({
        isLoading: false,
        isError: true,
        data: undefined,
        error: null,
    }));

    const {result} = renderHook(() => useEditableCatalogCriteria('123e4567-e89b-12d3-a456-426614174000'));
    expect(result.error).toEqual(Error('Unable to initialize editable catalog criteria from the backend state'));
});
