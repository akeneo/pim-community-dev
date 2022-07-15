jest.unmock('./factory');

import factory from './factory';
import {Operator} from '../../models/Operator';

test('it creates a FamilyCriterion state with default values', () => {
    expect(factory()).toMatchObject({
        field: 'family',
        operator: Operator.IN_LIST,
        value: [],
    });
});

test('it creates a FamilyCriterion state with given values', () => {
    expect(
        factory({
            operator: Operator.IS_NOT_EMPTY,
            value: [],
        })
    ).toMatchObject({
        field: 'family',
        operator: Operator.IS_NOT_EMPTY,
        value: [],
    });
});
