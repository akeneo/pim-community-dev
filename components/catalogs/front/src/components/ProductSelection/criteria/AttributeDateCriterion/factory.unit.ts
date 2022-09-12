jest.unmock('./factory');

import factory from './factory';
import {Operator} from '../../models/Operator';

test('it creates a AttributeDateCriterion state with default values', () => {
    expect(factory({field: 'released_at'})).toMatchObject({
        field: 'released_at',
        operator: Operator.IS_NOT_EMPTY,
        value: null,
        locale: null,
        scope: null,
    });
});

test('it creates a AttributeDateCriterion state with given values', () => {
    expect(
        factory({
            field: 'released_at',
            operator: Operator.GREATER_THAN,
            value: '2021-12-31',
            locale: 'en_US',
            scope: 'ecommerce',
        })
    ).toMatchObject({
        field: 'released_at',
        operator: Operator.GREATER_THAN,
        value: '2021-12-31',
        locale: 'en_US',
        scope: 'ecommerce',
    });
});

test('it throws if the factory is called without a given field', () => {
    expect(() => factory()).toThrow();
});
