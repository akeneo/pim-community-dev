import {isTableSource} from './model';

describe('it validates a Table source', () => {
  const tableSourceWithoutOperation = {
    uuid: '123',
    code: 'a code',
    type: 'attribute',
    locale: 'fr_FR',
    channel: 'ecommerce',
    operations: {},
    selection: {type: 'raw'},
  };

  test('it validates a Table source without operation', () => {
    expect(isTableSource(tableSourceWithoutOperation)).toBe(true);
  });

  test('it validates a Table source with default value operation', () => {
    const tableSourceWithDefaultValueOperation = {
      ...tableSourceWithoutOperation,
      operations: {
        default_value: {
          type: 'default_value',
          value: 'a default value',
        },
      },
    };

    expect(isTableSource(tableSourceWithDefaultValueOperation)).toBe(true);
  });

  test('it invalidates a Table source with invalid operation', () => {
    const tableSourceWithInvalidOperation = {
      ...tableSourceWithoutOperation,
      operations: {
        foo: 'bar',
      },
    };

    expect(isTableSource(tableSourceWithInvalidOperation)).toBe(false);
  });
});
