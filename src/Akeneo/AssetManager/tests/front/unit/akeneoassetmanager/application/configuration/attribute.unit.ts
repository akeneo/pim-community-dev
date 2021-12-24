import {
  getTypes,
  getIcon,
  getView,
  getDenormalizer,
  getReducer,
} from 'akeneoassetmanager/application/configuration/attribute';
import {AttributeConfig} from "../../../../../../front/application/configuration/attribute";
import * as TextDenormalize from 'akeneoassetmanager/domain/model/attribute/type/text';
import * as TextReducer from 'akeneoassetmanager/application/reducer/attribute/type/text';
import * as MediaFileDenormalize from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import * as MediaFileReducer from 'akeneoassetmanager/application/reducer/attribute/type/media-file';
import {
  NormalizedTextAttribute,
  TextAttribute,
  ValidationRuleOption
} from "../../../../../../front/domain/model/attribute/type/text";

const baseNormalizedAttribute: NormalizedTextAttribute = {
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
  regular_expression: null
};

const baseAttribute: TextAttribute = {
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
  normalize: () => baseNormalizedAttribute,
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
  regularExpression: null
};

const baseAttributeConfig: AttributeConfig = {
  text: {
    icon: 'bundles/pimui/images/attribute/icon-text.svg',
    denormalize: TextDenormalize,
    reducer: TextReducer,
    view: require('akeneoassetmanager/application/component/attribute/edit/text'),
  },
  media_file: {
    icon: 'bundles/pimui/images/attribute/icon-mediafile.svg',
    denormalize: MediaFileDenormalize,
    reducer: MediaFileReducer,
    view: require('akeneoassetmanager/application/component/attribute/edit/media-file'),
  },
};

describe('akeneo > asset family > application > configuration --- attribute', () => {
  test('I can get an attribute denormalizer', () => {
    const getAttributeDenormalizer = getDenormalizer(baseAttributeConfig);

    const normalizedAttribute = {...baseNormalizedAttribute, code: 'attribute_to_denormalize'};
    expect(getAttributeDenormalizer(normalizedAttribute)(normalizedAttribute)).toEqual({
      "assetFamilyIdentifier": "packshot",
      "code": "attribute_to_denormalize",
      "identifier": "attribute_identifier",
      "isReadOnly": false,
      "isRequired": false,
      "isRichTextEditor": false,
      "isTextarea": false,
      "labelCollection": {},
      "maxLength": 1,
      "order": 2,
      "regularExpression": null,
      "type": "text",
      "validationRule": "none",
      "valuePerChannel": false,
      "valuePerLocale": false
    });
  });

  test('I get an error if the configuration does not have an proper text denormalizer', () => {
    const getAttributeDenormalizer = getDenormalizer({
      text: {
        // @ts-expect-error invalid attribute configuration
        denormalize: {},
      },
    });

    const normalizedAttribute = {...baseNormalizedAttribute, code: 'attribute_to_denormalize'};
    expect(() => {
      getAttributeDenormalizer(normalizedAttribute);
    }).toThrowError(`The module you are exposing to denormalize an attribute of type "text" needs to
export a "denormalize" property. Here is an example of a valid denormalize es6 module:

export const denormalize = (normalizedTextAttribute: NormalizedAttribute) => {
  return new TextAttribute(normalizedTextAttribute);
};`);
  });

  test('I get an error if the configuration does not have valid configurations', () => {
    const getAttributeDenormalizer = getDenormalizer({
      // @ts-expect-error invalid attribute configuration
      text: {},
    });

    const normalizedAttribute = {...baseNormalizedAttribute, code: 'attribute_to_denormalize'};
    expect(() => {
      getAttributeDenormalizer(normalizedAttribute);
    }).toThrowError(`Cannot get the attribute denormalizer for type "text". The configuration should look like this:
config:
    config:
        akeneoassetmanager/application/configuration/attribute:
            text:
                denormalize: '@my_attribute_denormalizer'

Actual conf: ${JSON.stringify({text: {}})}`);
  });

  test('I can get an attribute view', () => {
    const View = require('akeneoassetmanager/application/component/attribute/edit/media-file.tsx');
    const attributeConfig: AttributeConfig = {
      text: {
        ...baseAttributeConfig.text,
        view: {
          view: View,
        },
      },
    };

    const attribute = {...baseAttribute, code: 'attribute_to_render', getType: () => 'text'};
    expect(getView(attributeConfig, attribute)).toBe(View);
  });

  test('I get an error if the configuration does not have an proper text view', () => {
    const attributeConfig: AttributeConfig = {
      text: {
        // @ts-expect-error invalid attribute configuration
        view: {},
      },
    };

    const attribute = {...baseAttribute, code: 'attribute_to_render', getType: () => 'text'};
    expect(() => {
      getView(attributeConfig, attribute);
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

    const attribute = {...baseAttribute, code: 'attribute_to_render', getType: () => 'text'};
    expect(() => {getView(attributeConfig, attribute)})
      .toThrowError(`Cannot get the attribute view for type "text". The configuration should look like this:
config:
    config:
        akeneoassetmanager/application/configuration/attribute:
            text:
                view: '@my_attribute_view'

Actual conf: ${JSON.stringify({text: {}})}`);
  });

  test('I can get an attribute reducer', () => {
    const getAttributeReducer = getReducer(baseAttributeConfig);

    const attribute = {...baseNormalizedAttribute, code: 'attribute_to_reduce'};
    expect(getAttributeReducer(attribute)(attribute, 'max_length', 10)).toEqual({...attribute, max_length: 10});
  });

  test('I get an error if the configuration does not have an proper text cell view', () => {
    const getAttributeReducer = getReducer({
      text: {
        // @ts-expect-error invalid attribute configuration
        reducer: {},
      },
    });

    expect(() => {
      getAttributeReducer(baseNormalizedAttribute);
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
      getAttributeReducer({...baseNormalizedAttribute});
    }).toThrowError(`Cannot get the attribute reducer for type "text". The configuration should look like this:
config:
    config:
        akeneoassetmanager/application/configuration/attribute:
            text:
                reducer: '@my_attribute_reducer'

Actual conf: ${JSON.stringify({text: {}})}`);
  });

  test('I can get the list of the attribute types', () => {
    const attributeConfig: AttributeConfig = {
      text: {
        ...baseAttributeConfig.text,
        icon: 'icon.svg',
      },
      media_file: {
        ...baseAttributeConfig.media_file,
        icon: 'icon.svg',
      },
    };

    expect(getTypes(attributeConfig)).toEqual([
      {icon: 'icon.svg', identifier: 'text', label: 'pim_asset_manager.attribute.type.text'},
      {icon: 'icon.svg', identifier: 'media_file', label: 'pim_asset_manager.attribute.type.media_file'},
    ]);
  });

  test('I can get an attribute icon', () => {
    const attributeConfig: AttributeConfig = {
      text: {
        ...baseAttributeConfig.text,
        icon: 'icon_text.svg',
      },
      media_file: {
        ...baseAttributeConfig.media_file,
        icon: 'icon_image.svg',
      },
    };

    expect(getIcon(attributeConfig, 'text')).toEqual('icon_text.svg');
  });
});
