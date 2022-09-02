import {
  getFieldView,
  getFilterViews,
  getFilterView,
  ValueConfig,
} from 'akeneoassetmanager/application/configuration/value';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import {NormalizedTextAttribute, ValidationRuleOption} from 'akeneoassetmanager/domain/model/attribute/type/text';
import {view as TextEditView} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/text';
import {fakeConfig} from '../../utils/FakeConfigProvider';

const textAttribute: NormalizedTextAttribute = {
  code: 'attribute_code',
  type: 'text',
  asset_family_identifier: 'packshot',
  labels: {},
  value_per_locale: false,
  value_per_channel: false,
  identifier: 'attribute_identifier',
  order: 2,
  is_required: false,
  is_read_only: false,
  max_length: 1,
  is_textarea: false,
  is_rich_text_editor: false,
  validation_rule: ValidationRuleOption.None,
  regular_expression: null,
};

const editionValue: EditionValue = {
  data: 'data to render',
  channel: 'ecommerce',
  locale: 'en_US',
  attribute: textAttribute,
};

describe('akeneo > asset family > application > configuration --- value', () => {
  test('I can get a value view', () => {
    expect(getFieldView(fakeConfig.value, editionValue)).toBe(TextEditView);
  });

  test('I get an error if the configuration does not have an proper text view', () => {
    const valueConfig: ValueConfig = {
      text: {
        // @ts-expect-error invalid value configuration
        view: {},
      },
    };

    expect(() => {
      getFieldView(valueConfig, editionValue);
    }).toThrowError(`The module you are exposing to provide a view for a data of type "text" needs to
export a "view" property. Here is an example of a valid view es6 module for the "text" type:

export const view = (value: TextValue, onChange: (value: Value) => void) => {
  return <input value={value.getData()} onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
    onChange(value.setData(createTextData(event.currentTarget.value)));
  }} />;
};`);
  });

  test('I get an error if the configuration does not have valid configurations', () => {
    const valueConfig: ValueConfig = {
      // @ts-expect-error invalid value configuration
      text: {},
    };

    expect(() => {
      getFieldView(valueConfig, editionValue);
    }).toThrowError(`Cannot get the data field view generator for type "text". The configuration should look like this:
config:
    config:
        akeneoassetmanager/application/configuration/value:
            text:
                view: '@my_data_view'

Actual conf: ${JSON.stringify({text: {}})}`);
  });

  test('I can get a filter value list of views', () => {
    const attributes = [
      textAttribute,
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
        is_read_only: false,
      },
    ];

    expect(getFilterViews(fakeConfig.value, attributes)[0].attribute.code).toEqual('color');
  });

  test('I get an error if the filter view configuration does not exist', () => {
    expect(() => {
      getFilterView(fakeConfig.value, 'text');
    }).toThrowError(`Cannot get the data filter view generator for type "text". The configuration should look like this:
config:
    config:
        akeneoassetmanager/application/configuration/value:
            text:
                filter: '@my_data_filter_view'

Actual conf: ${JSON.stringify({text: {view: {}}, option: {view: {}, filter: {filter: {compare: null}}}})}`);
  });

  test('I get an error if the filter view configuration is invalid', () => {
    const valueConfig: ValueConfig = {
      option: {
        // @ts-expect-error invalid value configuration
        filter: {},
      },
    };

    expect(() => {
      getFilterView(valueConfig, 'option');
    }).toThrowError(`The module you are exposing to provide a view for a data of type \"option\" needs to
export a \"filter\" property. Here is an example of a valid view es6 module for the \"option\" type:
export const filter = (value: NormalizedOptionValue) => {
  return <span>{{value.getData()}}</span>;
};`);
  });
});
