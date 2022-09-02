jest.unmock('./factory');

import factory from './factory';
import {Operator} from '../../models/Operator';

test('it creates a AttributeBooleanCriterion state with default values', () => {
    expect(factory({field: 'name'})).toMatchObject({
        field: 'name',
        operator: Operator.EQUALS,
        value: true,
        locale: null,
        scope: null,
    });
});

test('it creates a AttributeBooleanCriterion state with given values', () => {
    expect(
        factory({
            field: 'name',
            operator: Operator.NOT_EQUAL,
            value: false,
            locale: 'en_US',
            scope: 'ecommerce',
        })
    ).toMatchObject({
        field: 'name',
        operator: Operator.NOT_EQUAL,
        value: false,
        locale: 'en_US',
        scope: 'ecommerce',
    });
});

test('it throws if the factory is called without a given field', () => {
    expect(() => factory()).toThrow();
});
