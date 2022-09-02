import {
  getTypes,
  getIcon,
  getView,
  getDenormalizer,
  getReducer,
} from 'akeneoassetmanager/application/configuration/attribute';
import {AttributeConfig} from 'akeneoassetmanager/application/configuration/attribute';
import {
  NormalizedTextAttribute,
  TextAttribute,
  ValidationRuleOption,
} from 'akeneoassetmanager/domain/model/attribute/type/text';
import {view as TextInputView} from 'akeneoassetmanager/application/component/attribute/edit/text';
import {fakeConfig} from '../../utils/FakeConfigProvider';

const normalizedTextAttribute: NormalizedTextAttribute = {
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

const textAttribute: TextAttribute = {
  assetFamilyIdentifier: 'packshot',
  code: 'attribute_code',
  labelCollection: {},
  type: 'text',
  valuePerLocale: false,
  valuePerChannel: false,
  getCode: () => 'attribute_code',
  getAssetFamilyIdentifier: () => 'packshot',
  getType: () => 'text',
  getLabel: (_locale: string, _fallbackOnCode?: boolean) => 'attribute_code',
  getLabelCollection: () => ({}),
  normalize: () => normalizedTextAttribute,
  identifier: 'attribute_identifier',
  order: 2,
  isRequired: false,
  isReadOnly: false,
  equals: () => false,
  getIdentifier: () => 'attribute_identifier',
  maxLength: 1,
  isTextarea: false,
  isRichTextEditor: false,
  validationRule: ValidationRuleOption.None,
  regularExpression: null,
};

describe('akeneo > asset family > application > configuration --- attribute', () => {
  test('I can get an attribute denormalizer', () => {
    const getAttributeDenormalizer = getDenormalizer(fakeConfig.attribute, normalizedTextAttribute);

    expect(getAttributeDenormalizer(normalizedTextAttribute)).toEqual({
      assetFamilyIdentifier: 'packshot',
      code: 'attribute_code',
      identifier: 'attribute_identifier',
      isReadOnly: false,
      isRequired: false,
      isRichTextEditor: false,
      isTextarea: false,
      labelCollection: {},
      maxLength: 1,
      order: 2,
      regularExpression: null,
      type: 'text',
      validationRule: 'none',
      valuePerChannel: false,
      valuePerLocale: false,
    });
  });

  test('I get an error if the configuration does not have an proper text denormalizer', () => {
    expect(() => {
      const getAttributeDenormalizer = getDenormalizer(
        {
          text: {
            // @ts-expect-error invalid attribute configuration
            denormalize: {},
          },
        },
        normalizedTextAttribute
      );

      getAttributeDenormalizer(normalizedTextAttribute);
    }).toThrowError(`The module you are exposing to denormalize an attribute of type "text" needs to
export a "denormalize" property. Here is an example of a valid denormalize es6 module:

export const denormalize = (normalizedTextAttribute: NormalizedAttribute) => {
  return new TextAttribute(normalizedTextAttribute);
};`);
  });

  test('I get an error if the configuration does not have valid configurations', () => {
    expect(() => {
      const getAttributeDenormalizer = getDenormalizer(
        {
          // @ts-expect-error invalid attribute configuration
          text: {},
        },
        normalizedTextAttribute
      );

      getAttributeDenormalizer(normalizedTextAttribute);
    }).toThrowError(`Cannot get the attribute denormalizer for type "text". The configuration should look like this:
config:
    config:
        akeneoassetmanager/application/configuration/attribute:
            text:
                denormalize: '@my_attribute_denormalizer'

Actual conf: ${JSON.stringify({text: {}})}`);
  });

  test('I can get an attribute view', () => {
    expect(getView(fakeConfig.attribute, textAttribute)).toBe(TextInputView);
  });

  test('I get an error if the configuration does not have an proper text view', () => {
    const attributeConfig: AttributeConfig = {
      text: {
        // @ts-expect-error invalid attribute configuration
        view: {},
      },
    };

    expect(() => {
      getView(attributeConfig, textAttribute);
    }).toThrowError(`
const TextView = ({
  attribute,
  onAdditionalPropertyUpdated,
  onSubmit,
  errors,
}: {
  attribute: TextAttribute;
  onAdditionalPropertyUpdated: (property: string, value: TextAdditionalProperty) => void;
  onSubmit: () => void;
  errors: ValidationError[];
}) => {
  return (
    <React.Fragment>
      <div className="AknFieldContainer">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label className="AknFieldContainer-label" htmlFor="pim_asset_manager.attribute.edit.input.can_be_null">
            {__('pim_asset_manager.attribute.edit.input.can_be_null')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <input
            type="text"
            className="AknTextField AknTextField--light"
            id="pim_asset_manager.attribute.edit.input.can_be_null"
            name="can_be_null"
            value={attribute.canBeNull.stringValue()}
            onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
              if ('Enter' === event.key) {
                onSubmit();
              }
            }}
            onChange={(event: React.FormEvent<HTMLInputElement>) => {
              if (!MaxFileSize.isValid(event.currentTarget.value)) {
                event.currentTarget.value = attribute.canBeNull.stringValue();
                event.preventDefault();
                return;
              }

              onAdditionalPropertyUpdated('can_be_null', MaxFileSize.createFromString(event.currentTarget.value));
            }}
          />
        </div>
        {getErrorsView(errors, 'canBeNull')}
      </div>
    </React.Fragment>
  );
};

export view = TextView;`);
  });

  test('I get an error if the configuration does not have valid configurations', () => {
    const attributeConfig: AttributeConfig = {
      // @ts-expect-error invalid attribute configuration
      text: {},
    };

    expect(() => {
      getView(attributeConfig, textAttribute);
    }).toThrowError(`Cannot get the attribute view for type "text". The configuration should look like this:
config:
    config:
        akeneoassetmanager/application/configuration/attribute:
            text:
                view: '@my_attribute_view'

Actual conf: ${JSON.stringify({text: {}})}`);
  });

  test('I can get an attribute reducer', () => {
    const getAttributeReducer = getReducer(fakeConfig.attribute);

    expect(getAttributeReducer(normalizedTextAttribute)(normalizedTextAttribute, 'max_length', 10)).toEqual({
      ...normalizedTextAttribute,
      max_length: 10,
    });
  });

  test('I get an error if the configuration does not have an proper text cell view', () => {
    const getAttributeReducer = getReducer({
      text: {
        // @ts-expect-error invalid attribute configuration
        reducer: {},
      },
    });

    expect(() => {
      getAttributeReducer(normalizedTextAttribute);
    }).toThrowError(`The module you are exposing as reducer for attribute of type "text" needs to
export a "reducer" property. Here is an example of a valid reducer es6 module:

export reducer = (
  normalizedAttribute: NormalizedTextAttribute,
  propertyCode: string,
  propertyValue: NormalizedTextAdditionalProperty
): NormalizedTextAttribute => {
  switch (propertyCode) {
    case 'can_be_null':
      const can_be_null = propertyValue as NormalizedCanBeNull;
      return {...normalizedAttribute, can_be_null};

    default:
      break;
  }

  return normalizedAttribute;
};`);
  });

  test('I get an error if the configuration does not have valid configurations', () => {
    const getAttributeReducer = getReducer({
      // @ts-expect-error invalid attribute configuration
      text: {},
    });

    expect(() => {
      getAttributeReducer(normalizedTextAttribute);
    }).toThrowError(`Cannot get the attribute reducer for type "text". The configuration should look like this:
config:
    config:
        akeneoassetmanager/application/configuration/attribute:
            text:
                reducer: '@my_attribute_reducer'

Actual conf: ${JSON.stringify({text: {}})}`);
  });

  test('I can get the list of the attribute types', () => {
    expect(getTypes(fakeConfig.attribute)).toEqual([
      {
        icon: 'bundles/pimui/images/attribute/icon-text.svg',
        identifier: 'text',
        label: 'pim_asset_manager.attribute.type.text',
      },
      {
        icon: 'bundles/pimui/images/attribute/icon-mediafile.svg',
        identifier: 'media_file',
        label: 'pim_asset_manager.attribute.type.media_file',
      },
      {
        icon: 'bundles/pimui/images/attribute/icon-select.svg',
        identifier: 'option',
        label: 'pim_asset_manager.attribute.type.option',
      },
      {
        icon: 'bundles/pimui/images/attribute/icon-multiselect.svg',
        identifier: 'option_collection',
        label: 'pim_asset_manager.attribute.type.option_collection',
      },
      {
        icon: 'bundles/pimui/images/attribute/icon-number.svg',
        identifier: 'number',
        label: 'pim_asset_manager.attribute.type.number',
      },
    ]);
  });

  test('I can get an attribute icon', () => {
    expect(getIcon(fakeConfig.attribute, 'text')).toEqual('bundles/pimui/images/attribute/icon-text.svg');
  });
});
