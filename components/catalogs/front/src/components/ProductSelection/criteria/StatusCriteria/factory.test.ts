jest.unmock('./factory');

import factory from './factory';
import {Operator} from '../../models/Operator';

test('it creates a StatusCriterion with default values', () => {
    expect(factory()).toMatchObject({
        id: expect.any(String),
        module: expect.any(Function),
        field: 'status',
        operator: Operator.EQUALS,
        value: true,
    });
});

test('it creates a StatusCriterion with different values', () => {
    expect(
        factory({
            operator: Operator.NOT_EQUAL,
            value: false,
        })
    ).toMatchObject({
        id: expect.any(String),
        module: expect.any(Function),
        field: 'status',
        operator: Operator.NOT_EQUAL,
        value: false,
    });
});
