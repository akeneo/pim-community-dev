import Value, {NormalizedValue} from 'akeneoenrichedentity/domain/model/record/value';

export class InvalidArgument extends Error {}

type Denormalizer = (normalizedValue: NormalizedValue) => Value;
type ViewGenerator = (value: Value) => any;

type ValueDenormalizerConfig = {
  [type: string]: {
    denormalize: {
      denormalize: Denormalizer;
    };
    view: {
      view: ViewGenerator;
    };
  };
};

const getDenormalizer = (config: ValueDenormalizerConfig) => (normalizedValue: NormalizedValue): Denormalizer => {
  const denormalizeData = config[normalizedValue.attribute.type].denormalize.denormalize;

  if (undefined === denormalizeData) {
    const confPath = `
    config:
        config:
            akeneoenrichedentity/application/configuration/value:
            ${normalizedValue.attribute.type}:
                denormalize: '@my_data_denormalizer'`;

    throw new InvalidArgument(
      `Cannot get the data denormalizer for type ${
        normalizedValue.attribute.type
      }. The configuration should look like this: ${confPath}

Actual conf: ${JSON.stringify(config)}`
    );
  }

  return denormalizeData;
};

const getView = (config: ValueDenormalizerConfig) => (value: Value): ViewGenerator => {
  const view = config[value.attribute.getType()].view.view;

  if (undefined === view) {
    const confPath = `
    config:
        config:
            akeneoenrichedentity/application/configuration/value:
            ${value.attribute.getType()}:
                view: '@my_data_view'`;

    throw new InvalidArgument(
      `Cannot get the data view generator for type ${value.attribute.getType()}. The configuration should look like this: ${confPath}

Actual conf: ${JSON.stringify(config)}`
    );
  }

  return view;
};

export const getDataDenormalizer = getDenormalizer(__moduleConfig as ValueDenormalizerConfig);
export const getDataView = getView(__moduleConfig as ValueDenormalizerConfig);
