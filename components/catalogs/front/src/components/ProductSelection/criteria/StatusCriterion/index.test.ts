jest.unmock('./index');

import factory from './index';
import {Operator} from '../../models/Operator';

test('it creates a StatusCriterion with default values', () => {
    expect(factory()).toMatchObject({
        id: expect.any(String),
        module: expect.any(Function),
        state: {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: true,
        },
    });
});

test('it creates a StatusCriterion with empty values', () => {
    expect(
        factory({
            operator: undefined,
            value: undefined,
        })
    ).toMatchObject({
        id: expect.any(String),
        module: expect.any(Function),
        state: {
            field: 'enabled',
            operator: Operator.EQUALS,
            value: true,
        },
    });
});

test('it creates a StatusCriterion with given values', () => {
    expect(
        factory({
            operator: Operator.NOT_EQUAL,
            value: false,
        })
    ).toMatchObject({
        id: expect.any(String),
        module: expect.any(Function),
        state: {
            field: 'enabled',
            operator: Operator.NOT_EQUAL,
            value: false,
        },
    });
});
