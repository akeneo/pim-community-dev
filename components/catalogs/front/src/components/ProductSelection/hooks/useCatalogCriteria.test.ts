jest.unmock('./useCatalogCriteria');

import {renderHook} from '@testing-library/react-hooks';
import {useCatalogCriteria} from './useCatalogCriteria';
import {Operator} from '../models/Operator';
import StatusCriterion from '../criteria/StatusCriterion';

test("it fetches a catalog's criteria", () => {
    (StatusCriterion as jest.Mock).mockImplementation(() => ({
        operator: Operator.EQUALS,
        value: true,
    }));

    const {result} = renderHook(() => useCatalogCriteria('c00a6ef5-da23-4dbe-83a2-98ccc7075890'));

    expect(result.current).toMatchObject([
        {
            operator: Operator.EQUALS,
            value: true,
        },
    ]);
});
