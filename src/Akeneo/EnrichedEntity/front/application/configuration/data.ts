import Value, {NormalizedValue} from 'akeneoenrichedentity/domain/model/record/value';

export class InvalidArgument extends Error {}

type ValueDenormalizerConfig = {
  [type: string]: {
    denormalize: (normalizedValue: NormalizedValue) => Value;
  };
};

const getDenormalizer = (config: ValueDenormalizerConfig) => (
  normalizedValue: NormalizedValue
): ((normalizedValue: NormalizedValue) => Value) => {
  const denormalizeData = config[normalizedValue.attribute.type].denormalize;

  if (undefined === denormalizeData) {
    const confPath = `
    config:
        config:
            akeneoenrichedentity/application/configuration/value:
                denormalize:
                    ${normalizedValue.attribute.type}: '@my_data_denormalizer'`;

    throw new InvalidArgument(
      `Cannot get the data denormalizer for type ${
        normalizedValue.attribute.type
      }. The configuration should look like this: ${confPath}

Actual conf: ${JSON.stringify(config)}`
    );
  }

  return denormalizeData;
};

export const getDataDenormalizer = getDenormalizer(__moduleConfig as ValueDenormalizerConfig);
