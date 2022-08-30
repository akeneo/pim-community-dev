jest.unmock('./useFindCriterionByField');
jest.unmock('./useFindAttributeCriterionByType');

import {renderHook} from '@testing-library/react-hooks';
import fetchMock from 'jest-fetch-mock';
import {ReactQueryWrapper} from '../../../../tests/ReactQueryWrapper';
import {useFindCriterionByField} from './useFindCriterionByField';
import {AnyCriterion} from '../models/Criterion';
import StatusCriterion from '../criteria/StatusCriterion';
import FamilyCriterion from '../criteria/FamilyCriterion';
import CompletenessCriterion from '../criteria/CompletenessCriterion';
import CategoryCriterion from '../criteria/CategoryCriterion';

const criterions: [string, AnyCriterion][] = [
    ['enabled', StatusCriterion],
    ['family', FamilyCriterion],
    ['completeness', CompletenessCriterion],
    ['categories', CategoryCriterion],
];

test.each(criterions)('it returns a criterion when searching for "%s"', async (field, criterion) => {
    const {result} = renderHook(() => useFindCriterionByField(), {
        wrapper: ReactQueryWrapper,
    });

    expect(await result.current(field)).toMatchObject(criterion);
});

test('it throws when searching for an unknown field', async () => {
    // mute the error in the output
    jest.spyOn(console, 'error');
    /* eslint-disable-next-line no-console */
    (console.error as jest.Mock).mockImplementation(() => null);

    const {result} = renderHook(() => useFindCriterionByField(), {
        wrapper: ReactQueryWrapper,
    });

    await expect(async () => await result.current('foo')).rejects.not.toBeNull();
});

test('it returns a criterion when searching for an attribute code', async () => {
    fetchMock.mockResponseOnce(
        JSON.stringify({
            code: 'name',
            label: 'Name',
            type: 'pim_catalog_text',
            scopable: false,
            localizable: false,
        })
    );

    const {result} = renderHook(() => useFindCriterionByField(), {
        wrapper: ReactQueryWrapper,
    });

    expect(await result.current('name')).toMatchObject({
        component: expect.any(Function),
        factory: expect.any(Function),
    });
});
