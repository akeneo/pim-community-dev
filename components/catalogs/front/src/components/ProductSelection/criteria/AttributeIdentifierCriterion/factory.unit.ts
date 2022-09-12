jest.unmock('./factory');

import factory from './factory';
import {Operator} from '../../models/Operator';

test('it creates a AttributeIdentifierCriterion state with default values', () => {
    expect(factory({field: 'sku'})).toMatchObject({
        field: 'sku',
        operator: Operator.IN_LIST,
        value: [],
        locale: null,
        scope: null,
    });
});

test('it creates a AttributeIdentifierCriterion state with given values', () => {
    expect(
        factory({
            field: 'sku',
            operator: Operator.NOT_IN_LIST,
            value: ['foo'],
            locale: 'en_US',
            scope: 'ecommerce',
        })
    ).toMatchObject({
        field: 'sku',
        operator: Operator.NOT_IN_LIST,
        value: ['foo'],
        locale: 'en_US',
        scope: 'ecommerce',
    });
});

test('it throws if the factory is called without a given field', () => {
    expect(() => factory()).toThrow();
});
