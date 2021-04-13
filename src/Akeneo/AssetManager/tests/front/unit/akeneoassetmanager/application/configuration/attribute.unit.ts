import {
  getTypes,
  getIcon,
  getView,
  getDenormalizer,
  getReducer,
} from 'akeneoassetmanager/application/configuration/attribute';

jest.mock('require-context', name => {});

describe('akeneo > asset family > application > configuration --- attribute', () => {
  test('I can get an attribute denormalizer', () => {
    const getAttributeDenormalizer = getDenormalizer({
      text: {
        denormalize: {
          denormalize: normalizedAttribute => {
            expect(normalizedAttribute.code).toEqual('attribute_to_denormalize');

            return true;
          },
        },
      },
    });

    const normalizedAttribute = {code: 'attribute_to_denormalize', type: 'text'};
    expect(getAttributeDenormalizer(normalizedAttribute)(normalizedAttribute)).toBe(true);
    expect.assertions(2);
  });

  test('I get an error if the configuration does not have an proper text denormalizer', () => {
    const getAttributeDenormalizer = getDenormalizer({
      text: {
        denormalize: {},
      },
    });

    const normalizedAttribute = {code: 'attribute_to_denormalize', type: 'text'};
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
      text: {},
    });

    const normalizedAttribute = {code: 'attribute_to_denormalize', type: 'text'};
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
    const getAttributeView = getView({
      text: {
        view: {
          view: attribute => {
            expect(attribute.code).toEqual('attribute_to_render');

            return true;
          },
        },
      },
    });

    const attribute = {code: 'attribute_to_render', getType: () => 'text'};
    expect(getAttributeView(attribute)(attribute)).toBe(true);
    expect.assertions(2);
  });

  test('I get an error if the configuration does not have an proper text view', () => {
    const getAttributeView = getView({
      text: {
        view: {},
      },
    });

    const attribute = {code: 'attribute_to_render', getType: () => 'text'};
    expect(() => {
      getAttributeView(attribute);
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
    const getAttributeView = getView({
      text: {},
    });

    const attribute = {code: 'attribute_to_render', getType: () => 'text'};
    expect(() => {
      getAttributeView(attribute);
    }).toThrowError(`Cannot get the attribute view for type "text". The configuration should look like this:
config:
    config:
        akeneoassetmanager/application/configuration/attribute:
            text:
                view: '@my_attribute_view'

Actual conf: ${JSON.stringify({text: {}})}`);
  });

  test('I can get an attribute reducer', () => {
    const getAttributeReducer = getReducer({
      text: {
        reducer: {
          reducer: attribute => {
            expect(attribute.code).toEqual('attribute_to_reduce');

            return true;
          },
        },
      },
    });

    const attribute = {code: 'attribute_to_reduce', type: 'text'};
    expect(getAttributeReducer(attribute)(attribute)).toBe(true);
    expect.assertions(2);
  });

  test('I get an error if the configuration does not have an proper text cell view', () => {
    const getAttributeReducer = getReducer({
      text: {
        reducer: {},
      },
    });

    expect(() => {
      getAttributeReducer({type: 'text'});
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
      text: {},
    });

    expect(() => {
      getAttributeReducer({type: 'text'});
    }).toThrowError(`Cannot get the attribute reducer for type "text". The configuration should look like this:
config:
    config:
        akeneoassetmanager/application/configuration/attribute:
            text:
                reducer: '@my_attribute_reducer'

Actual conf: ${JSON.stringify({text: {}})}`);
  });

  test('I can get the list of the attribute types', () => {
    expect(
      getTypes({
        text: {
          icon: 'icon.svg',
          reducer: {},
        },
        media_file: {
          icon: 'icon.svg',
          reducer: {},
        },
      })()
    ).toEqual([
      {icon: 'icon.svg', identifier: 'text', label: 'pim_asset_manager.attribute.type.text'},
      {icon: 'icon.svg', identifier: 'media_file', label: 'pim_asset_manager.attribute.type.media_file'},
    ]);
  });

  test('I can get an attribute icon', () => {
    expect(
      getIcon({
        text: {
          icon: 'icon_text.svg',
          reducer: {},
        },
        media_file: {
          icon: 'icon_image.svg',
          reducer: {},
        },
      })('text')
    ).toEqual('icon_text.svg');
  });
});
