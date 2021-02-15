import {
  getFieldView,
  hasFilterView,
  getFilterView,
  getFilterViews,
  getValueConfig
} from 'akeneoassetmanager/application/configuration/value';
import {denormalize as denormalizeTextAttribute} from 'akeneoassetmanager/domain/model/attribute/type/text';

jest.mock('require-context', name => {});

describe('akeneo > asset family > application > configuration --- value', () => {
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

    const value = {data: 'data to render', attribute: {type: 'text'}};
    expect(getValueView(value)(value)).toBe(true);
    expect.assertions(2);
  });
  test('I get an error if the configuration does not have an proper text view', () => {
    const getValueView = getFieldView({
      text: {
        view: {},
      },
    });

    const value = {data: 'data to render', attribute: {type: 'text'}};
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

    const value = {data: 'data to render', attribute: {type: 'text'}};
    expect(() => {
      getValueView(value);
    }).toThrowError(`Cannot get the data field view generator for type "text". The configuration should look like this:
config:
    config:
        akeneoassetmanager/application/configuration/value:
            text:
                view: '@my_data_view'

Actual conf: ${JSON.stringify({text: {}})}`);
  });

  test('I can get a filter value view', () => {
    const getDataFilterView = getFilterView({
      text: {
        filter: {
          filter: (attribute, filter, onFilterUpdated) => {
            expect(attribute.getCode()).toEqual('description');

            return true;
          },
        },
      },
    });

    const attribute = denormalizeTextAttribute({
      identifier: 'description_1234',
      asset_family_identifier: 'designer',
      code: 'description',
      labels: {en_US: 'Description'},
      type: 'text',
      order: 0,
      value_per_locale: true,
      value_per_channel: false,
      is_required: true,
      is_read_only: true,
      max_length: 0,
      is_textarea: false,
      is_rich_text_editor: false,
      validation_rule: 'email',
      regular_expression: null,
    });

    expect(getDataFilterView('text')(attribute)).toBe(true);
    expect.assertions(2);
  });

  test('I can get a filter value list of views', () => {
    const getDataFilterViews = getFilterViews({
      option: {
        filter: {
          filter: (attribute, filter, onFilterUpdated) => {
            expect(attribute.getCode()).toEqual('color');

            return true;
          },
        },
      },
    });

    const attributes = [
      {
        type: 'option',
        identifier: 'color_packshot_fingerprint',
        asset_family_identifier: 'packshot',
        code: 'color',
        order: 0,
        is_required: true,
        labels: {en_US: 'Color'},
        value_per_locale: false,
        value_per_channel: false,
        options: [],
      },
    ];

    expect(getDataFilterViews(attributes)[0].attribute.code).toEqual('color');
  });

  test('I get an error if the configuration does not have an proper text filter view', () => {
    const getDataFilterView = getFilterView({
      text: {
        filter: {},
      },
    });

    expect(() => {
      getDataFilterView('text');
    }).toThrowError(`The module you are exposing to provide a view for a data of type "text" needs to
export a "filter" property. Here is an example of a valid view es6 module for the "text" type:

export const filter = (value: NormalizedTextValue) => {
  return <span>{{value.getData()}}</span>;
};`);
  });

  test('I get an error if the configuration does not have valid configurations', () => {
    const getDataFilterView = getFilterView({
      text: {},
    });

    expect(() => {
      getDataFilterView('text');
    }).toThrowError(`Cannot get the data filter view generator for type "text". The configuration should look like this:
config:
    config:
        akeneoassetmanager/application/configuration/value:
            text:
                filter: '@my_data_filter_view'

Actual conf: ${JSON.stringify({text: {}})}`);
  });

  test('I can know if a value has a filter view', () => {
    expect(
      hasFilterView({
        text: {
          filter: {
            filter: value => {},
          },
        },
      })('text')
    ).toBe(true);
    expect(
      hasFilterView({
        text: {
          filter: {
            filter: value => {},
          },
        },
      })('image')
    ).toBe(false);
  });
});
