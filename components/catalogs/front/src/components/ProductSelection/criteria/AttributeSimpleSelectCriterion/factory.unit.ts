jest.unmock('./factory');

import factory from './factory';
import {Operator} from '../../models/Operator';

test('it creates a AttributeSimpleSelectCriterion state with default values', () => {
    expect(factory({field: 'color'})).toMatchObject({
        field: 'color',
        operator: Operator.IN_LIST,
        value: [],
        locale: null,
        scope: null,
    });
});

test('it creates a AttributeSimpleSelectCriterion state with given values', () => {
    expect(
        factory({
            field: 'color',
            operator: Operator.NOT_IN_LIST,
            value: ['foo'],
            locale: 'en_US',
            scope: 'ecommerce',
        })
    ).toMatchObject({
        field: 'color',
        operator: Operator.NOT_IN_LIST,
        value: ['foo'],
        locale: 'en_US',
        scope: 'ecommerce',
    });
});

test('it throws if the factory is called without a given field', () => {
    expect(() => factory()).toThrow();
});
