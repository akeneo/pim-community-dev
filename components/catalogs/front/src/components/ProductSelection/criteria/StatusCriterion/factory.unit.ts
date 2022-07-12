jest.unmock('./factory');

import factory from './factory';
import {Operator} from '../../models/Operator';

test('it creates a StatusCriterion state with default values', () => {
    expect(factory()).toMatchObject({
        field: 'enabled',
        operator: Operator.EQUALS,
        value: true,
    });
});

test('it creates a StatusCriterion state with given values', () => {
    expect(
        factory({
            operator: Operator.NOT_EQUAL,
            value: false,
        })
    ).toMatchObject({
        field: 'enabled',
        operator: Operator.NOT_EQUAL,
        value: false,
    });
});
