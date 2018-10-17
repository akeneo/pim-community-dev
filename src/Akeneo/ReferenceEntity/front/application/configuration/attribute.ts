import {NormalizedAttribute, Attribute} from 'akeneoreferenceentity/domain/model/attribute/attribute';

export class InvalidArgument extends Error {}

export type Denormalizer = (normalizedAttribute: NormalizedAttribute) => Attribute;

type AttributeConfig = {
  [type: string]: {
    denormalize: {
      denormalizeAttribute: Denormalizer;
    };
  };
};

export const getTypes = (config: AttributeConfig) => () => {
  return Object.keys(config);
};

export const getDenormalizer = (config: AttributeConfig) => (
  normalizedAttribute: NormalizedAttribute
): Denormalizer => {
  const typeConfiguration = config[normalizedAttribute.type];

  if (undefined === typeConfiguration || undefined === typeConfiguration.denormalize) {
    const confPath = `config:
    config:
        akeneoreferenceentity/application/configuration/attribute:
            ${normalizedAttribute.type}:
                denormalize: '@my_attribute_denormalizer'`;

    throw new InvalidArgument(
      `Cannot get the attribute denormalizer for type "${
        normalizedAttribute.type
      }". The configuration should look like this:
${confPath}

Actual conf: ${JSON.stringify(config)}`
    );
  }

  if (undefined === typeConfiguration.denormalize.denormalizeAttribute) {
    const moduleExample = `
export const denormalizeAttribute = (normalizedBooleanAttribute: NormalizedAttribute) => {
  return new BooleanAttribute(normalizedBooleanAttribute);
};
`;

    throw new InvalidArgument(
      `The module you are exposing to denormalize an attribute of type "${normalizedAttribute.type}" needs to
export a "denormalizeAttribute" property. Here is an example of a valid denormalize es6 module:
${moduleExample}`
    );
  }

  return typeConfiguration.denormalize.denormalizeAttribute;
};

export const getAttributeTypes = getTypes(__moduleConfig as AttributeConfig);
export const getAttributeDenormalizer = getDenormalizer(__moduleConfig as AttributeConfig);
