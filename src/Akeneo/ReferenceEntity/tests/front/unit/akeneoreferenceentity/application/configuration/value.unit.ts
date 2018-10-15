import {getDenormalizer, getFieldView} from 'akeneoreferenceentity/application/configuration/value';

jest.mock('require-context', name => {});

describe('akeneo > reference entity > application > configuration --- value', () => {
  test('I can get a value denormalizer', () => {
    const getValueDenormalizer = getDenormalizer({
      text: {
        denormalize: {
          denormalize: normalizedValue => {
            expect(normalizedValue.data).toEqual('data_to_denormalize');

            return true;
          },
        },
      },
    });

    const normalizedValue = {data: 'data_to_denormalize', attribute: {type: 'text'}};
    expect(getValueDenormalizer(normalizedValue)(normalizedValue)).toBe(true);
    expect.assertions(2);
  });
  test('I get an error if the configuration does not have an proper text denormalizer', () => {
    const getValueDenormalizer = getDenormalizer({
      text: {
        denormalize: {},
      },
    });

    const normalizedValue = {data: 'data_to_denormalize', attribute: {type: 'text'}};
    expect(() => {
      getValueDenormalizer(normalizedValue);
    }).toThrowError(`The module you are exposing to denormalize a value of type "text" needs to
export a "denormalize" property. Here is an example of a valid denormalize es6 module:

export const denormalize = (normalizedBooleanData: boolean) => {
  return new BooleanData(normalizedBooleanData);
};`);
  });

  test('I get an error if the configuration does not have valid configurations', () => {
    const getValueDenormalizer = getDenormalizer({
      text: {},
    });

    const normalizedValue = {data: 'data_to_denormalize', attribute: {type: 'text'}};
    expect(() => {
      getValueDenormalizer(normalizedValue);
    }).toThrowError(`Cannot get the value denormalizer for type "text". The configuration should look like this:
config:
    config:
        akeneoreferenceentity/application/configuration/value:
            text:
                denormalize: '@my_value_denormalizer'

Actual conf: ${JSON.stringify({text: {}})}`);
  });

  test('I can get a value view', () => {
    const getValueView = getFieldView({
      text: {
        view: {
          view: value => {
            expect(value.data).toEqual('data to render');

            return true;
          },
        },
      },
    });

    const value = {data: 'data to render', attribute: {getType: () => 'text'}};
    expect(getValueView(value)(value)).toBe(true);
    expect.assertions(2);
  });
  test('I get an error if the configuration does not have an proper text view', () => {
    const getValueView = getFieldView({
      text: {
        view: {},
      },
    });

    const value = {data: 'data to render', attribute: {getType: () => 'text'}};
    expect(() => {
      getValueView(value);
    }).toThrowError(`The module you are exposing to provide a view for a data of type "text" needs to
export a "view" property. Here is an example of a valid view es6 module for the "text" type:

export const view = (value: TextValue, onChange: (value: Value) => void) => {
  return <input value={value.getData()} onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
    onChange(value.setData(createTextData(event.currentTarget.value)));
  }} />;
};`);
  });

  test('I get an error if the configuration does not have valid configurations', () => {
    const getValueView = getFieldView({
      text: {},
    });

    const value = {data: 'data to render', attribute: {getType: () => 'text'}};
    expect(() => {
      getValueView(value);
    }).toThrowError(`Cannot get the data field view generator for type "text". The configuration should look like this:
config:
    config:
        akeneoreferenceentity/application/configuration/value:
            text:
                view: '@my_data_view'

Actual conf: ${JSON.stringify({text: {}})}`);
  });
});
