import {getFieldView, getFilterViews, ValueConfig} from "../../../../../../front/application/configuration/value";
import EditionValue from "../../../../../../front/domain/model/asset/edition-value";
import {NormalizedOptionAttribute} from "../../../../../../front/domain/model/attribute/type/option";
import {NormalizedTextAttribute, ValidationRuleOption} from "../../../../../../front/domain/model/attribute/type/text";

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

const baseValue: EditionValue = {
  data: 'data to render',
  channel : 'ecommerce',
  locale: 'en_US',
  attribute: textAttribute
}

describe('akeneo > asset family > application > configuration --- value', () => {
  test('I can get a value view', () => {
    const TextView = require('akeneoassetmanager/application/component/asset/edit/enrich/data/text.tsx');
    const valueConfig: ValueConfig = {
      text: {
        view: TextView,
      },
    };

    const value: EditionValue = {...baseValue, data: 'data to render'};
    expect(getFieldView(valueConfig, value)).toBe(TextView.view);
  });

  test('I get an error if the configuration does not have an proper text view', () => {
    const valueConfig: ValueConfig = {
      text: {
        // @ts-expect-error invalid value configuration
        view: {},
      },
    };

    const value = {...baseValue, data: 'data to render'};
    expect(() => {
      getFieldView(valueConfig, value);
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

    const value = {...baseValue, data: 'data to render'};
    expect(() => {
      getFieldView(valueConfig, value);
    }).toThrowError(`Cannot get the data field view generator for type "text". The configuration should look like this:
config:
    config:
        akeneoassetmanager/application/configuration/value:
            text:
                view: '@my_data_view'

Actual conf: ${JSON.stringify({text: {}})}`);
  });

  test('I can get a filter value list of views', () => {
    const valueConfig: ValueConfig = {
      text: {
        view: require('akeneoassetmanager/application/component/asset/edit/enrich/data/text.tsx'),
      },
      option: {
        view: require('akeneoassetmanager/application/component/asset/edit/enrich/data/option.tsx'),
        filter: {
          filter: (attribute, filter, onFilterUpdated) => {
            expect(attribute.getCode()).toEqual('color');

            return true;
          },
        },
      },
    };

    const attributes: NormalizedOptionAttribute[] | NormalizedTextAttribute[] = [
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
        is_read_only: false
      },
    ];

    expect(getFilterViews(valueConfig, attributes)[0].attribute.code).toEqual('color');
  });
});
