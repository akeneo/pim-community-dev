jest.unmock('./factory');

import factory from './factory';
import {Operator} from '../../models/Operator';

test('it creates a AttributeNumberCriterion state with default values', () => {
    expect(factory({field: 'number_battery_cells'})).toMatchObject({
        field: 'number_battery_cells',
        operator: Operator.EQUALS,
        value: null,
        locale: null,
        scope: null,
    });
});

test('it creates a AttributeNumberCriterion state with given values', () => {
    expect(
        factory({
            field: 'number_battery_cells',
            operator: Operator.EQUALS,
            value: 4,
            locale: 'en_US',
            scope: 'ecommerce',
        })
    ).toMatchObject({
        field: 'number_battery_cells',
        operator: Operator.EQUALS,
        value: 4,
        locale: 'en_US',
        scope: 'ecommerce',
    });
});

test('it throws if the factory is called without a given field', () => {
    expect(() => factory()).toThrow();
});
