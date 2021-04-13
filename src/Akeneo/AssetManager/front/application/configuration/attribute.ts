import {NormalizedAttribute, Attribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';

export class InvalidArgument extends Error {}

export type Denormalizer = (normalizedAttribute: NormalizedAttribute) => Attribute;
export type View = React.SFC<{
  attribute: Attribute;
  onAdditionalPropertyUpdated: (property: string, value: any) => void;
  onSubmit: () => void;
  errors: ValidationError[];
  locale: string;
  rights: {
    attribute: {
      create: boolean;
      edit: boolean;
      delete: boolean;
    };
  };
}>;
export type Reducer = (
  normalizedAttribute: NormalizedAttribute,
  propertyCode: string,
  propertyValue: any
) => NormalizedAttribute;

type AttributeConfig = {
  [type: string]: {
    icon: string;
    denormalize: {
      denormalize: Denormalizer;
    };
    reducer: {
      reducer: Reducer;
    };
    view: {
      view: View;
    };
  };
};

export type AttributeType = {
  identifier: string;
  label: string;
  icon: string;
};

export const getTypes = (config: AttributeConfig) => (): AttributeType[] => {
  return Object.keys(config).map((identifier: string) => {
    return {
      identifier,
      label: `pim_asset_manager.attribute.type.${identifier}`,
      icon: config[identifier].icon,
    };
  });
};

export const getIcon = (config: AttributeConfig) => (attributeType: string): string => {
  return config[attributeType].icon;
};

export const getDenormalizer = (config: AttributeConfig) => (
  normalizedAttribute: NormalizedAttribute
): Denormalizer => {
  const typeConfiguration = config[normalizedAttribute.type];

  if (undefined === typeConfiguration || undefined === typeConfiguration.denormalize) {
    const expectedConfiguration = `config:
    config:
        akeneoassetmanager/application/configuration/attribute:
            ${normalizedAttribute.type}:
                denormalize: '@my_attribute_denormalizer'`;

    throw new InvalidArgument(
      `Cannot get the attribute denormalizer for type "${
        normalizedAttribute.type
      }". The configuration should look like this:
${expectedConfiguration}

Actual conf: ${JSON.stringify(config)}`
    );
  }

  if (undefined === typeConfiguration.denormalize.denormalize) {
    const capitalizedAttributeType =
      normalizedAttribute.type.charAt(0).toUpperCase() + normalizedAttribute.type.slice(1);
    const moduleExample = `
export const denormalize = (normalized${capitalizedAttributeType}Attribute: NormalizedAttribute) => {
  return new ${capitalizedAttributeType}Attribute(normalized${capitalizedAttributeType}Attribute);
};`;

    throw new InvalidArgument(
      `The module you are exposing to denormalize an attribute of type "${normalizedAttribute.type}" needs to
export a "denormalize" property. Here is an example of a valid denormalize es6 module:
${moduleExample}`
    );
  }

  return typeConfiguration.denormalize.denormalize;
};

export const getReducer = (config: AttributeConfig) => (normalizedAttribute: NormalizedAttribute): Reducer => {
  const typeConfiguration = config[normalizedAttribute.type];

  if (undefined === typeConfiguration || undefined === typeConfiguration.reducer) {
    const expectedConfiguration = `config:
    config:
        akeneoassetmanager/application/configuration/attribute:
            ${normalizedAttribute.type}:
                reducer: '@my_attribute_reducer'`;

    throw new InvalidArgument(
      `Cannot get the attribute reducer for type "${normalizedAttribute.type}". The configuration should look like this:
${expectedConfiguration}

Actual conf: ${JSON.stringify(config)}`
    );
  }

  if (undefined === typeConfiguration.reducer.reducer) {
    const capitalizedAttributeType =
      normalizedAttribute.type.charAt(0).toUpperCase() + normalizedAttribute.type.slice(1);
    const moduleExample = `
export reducer = (
  normalizedAttribute: Normalized${capitalizedAttributeType}Attribute,
  propertyCode: string,
  propertyValue: Normalized${capitalizedAttributeType}AdditionalProperty
): Normalized${capitalizedAttributeType}Attribute => {
  switch (propertyCode) {
    case 'can_be_null':
      const can_be_null = propertyValue as NormalizedCanBeNull;
      return {...normalizedAttribute, can_be_null};

    default:
      break;
  }

  return normalizedAttribute;
};`;

    throw new InvalidArgument(
      `The module you are exposing as reducer for attribute of type "${normalizedAttribute.type}" needs to
export a "reducer" property. Here is an example of a valid reducer es6 module:
${moduleExample}`
    );
  }

  return typeConfiguration.reducer.reducer;
};

export const getView = (config: AttributeConfig) => (attribute: Attribute): View => {
  const typeConfiguration = config[attribute.getType()];

  if (undefined === typeConfiguration || undefined === typeConfiguration.view) {
    const expectedConfiguration = `config:
    config:
        akeneoassetmanager/application/configuration/attribute:
            ${attribute.getType()}:
                view: '@my_attribute_view'`;

    throw new InvalidArgument(
      `Cannot get the attribute view for type "${attribute.getType()}". The configuration should look like this:
${expectedConfiguration}

Actual conf: ${JSON.stringify(config)}`
    );
  }

  if (undefined === typeConfiguration.view.view) {
    const capitalizedAttributeType =
      attribute
        .getType()
        .charAt(0)
        .toUpperCase() + attribute.getType().slice(1);
    const moduleExample = `
const ${capitalizedAttributeType}View = ({
  attribute,
  onAdditionalPropertyUpdated,
  onSubmit,
  errors,
}: {
  attribute: ${capitalizedAttributeType}Attribute;
  onAdditionalPropertyUpdated: (property: string, value: ${capitalizedAttributeType}AdditionalProperty) => void;
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

export view = TextView;`;

    throw new InvalidArgument(
      `The module you are exposing to view an attribute of type "${attribute.getType()}" needs to
export a "view" property. Here is an example of a valid view es6 module:
${moduleExample}`
    );
  }

  return typeConfiguration.view.view;
};

/**
 * Expanation about the __moduleConfig variable:
 * It is automatically added by a webpack loader that you can check here:
 * https://github.com/akeneo/pim-community-dev/blob/master/webpack/config-loader.js
 * This loader looks at the requirejs.yml file and find every configuration related to this module. It transform it
 * into a javascript object and add it automatically to the file on the fly.
 */
export const getAttributeTypes = getTypes(__moduleConfig as AttributeConfig);
export const getAttributeIcon = getIcon(__moduleConfig as AttributeConfig);
export const getAttributeView = getView(__moduleConfig as AttributeConfig);
export const getAttributeDenormalizer = getDenormalizer(__moduleConfig as AttributeConfig);
export const getAttributeReducer = getReducer(__moduleConfig as AttributeConfig);
