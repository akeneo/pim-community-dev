import {validateAgainstSchema} from '../../../../src/tools/validator';

console.error = jest.fn();

afterEach(() => {
  // @ts-ignore
  console.error.mockClear();
});

test('It validates data against schema', () => {
  const expectedData = {title: 'test_data'};
  const schema = {
    title: 'data',
    type: 'object',
    properties: {
      title: {
        type: 'string',
      },
    },
  };

  const data = validateAgainstSchema(expectedData, schema);

  expect(data).toEqual(expectedData);
});

test('It throws an error when the data are not valid', () => {
  const expectedData = {title: true};
  const schema = {
    title: 'data',
    type: 'object',
    properties: {
      title: {
        type: 'string',
      },
    },
  };

  expect(() => validateAgainstSchema(expectedData, schema)).toThrowError();
  expect(console.error).toHaveBeenCalledTimes(1);
});
