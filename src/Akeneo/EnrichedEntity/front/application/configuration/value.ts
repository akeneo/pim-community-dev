import Value, {NormalizedValue} from 'akeneoenrichedentity/domain/model/record/value';

export class InvalidArgument extends Error {}

export type Denormalizer = (normalizedValue: NormalizedValue) => Value;
export type ViewGenerator = (value: Value) => any;

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

export const getDenormalizer = (config: ValueDenormalizerConfig) => (
  normalizedValue: NormalizedValue
): Denormalizer => {
  const typeConfiguration = config[normalizedValue.attribute.type];

  if (undefined === typeConfiguration || undefined === typeConfiguration.denormalize) {
    const confPath = `config:
    config:
        akeneoenrichedentity/application/configuration/value:
            ${normalizedValue.attribute.type}:
                denormalize: '@my_value_denormalizer'`;

    throw new InvalidArgument(
      `Cannot get the value denormalizer for type "${
        normalizedValue.attribute.type
      }". The configuration should look like this:
${confPath}

Actual conf: ${JSON.stringify(config)}`
    );
  }

  if (undefined === typeConfiguration.denormalize.denormalize) {
    const moduleExample = `
export const denormalize = (normalizedBooleanData: boolean) => {
  return new BooleanData(normalizedBooleanData);
};
`;

    throw new InvalidArgument(
      `The module you are exposing to denormalize a value of type "${normalizedValue.attribute.type}" needs to
export a "denormalize" property. Here is an example of a valid denormalize es6 module:
${moduleExample}`
    );
  }

  return typeConfiguration.denormalize.denormalize;
};

export const getView = (config: ValueDenormalizerConfig) => (value: Value): ViewGenerator => {
  const attributeType = value.attribute.getType();
  const typeConfiguration = config[attributeType];

  if (undefined === typeConfiguration || undefined === typeConfiguration.view) {
    const confPath = `config:
    config:
        akeneoenrichedentity/application/configuration/value:
            ${attributeType}:
                view: '@my_data_view'`;

    throw new InvalidArgument(
      `Cannot get the data view generator for type "${attributeType}". The configuration should look like this:
${confPath}

Actual conf: ${JSON.stringify(config)}`
    );
  }

  if (undefined === typeConfiguration.view.view) {
    const capitalizedAttributeType = attributeType.charAt(0).toUpperCase() + attributeType.slice(1);
    const moduleExample = `
export const view = (value: ${capitalizedAttributeType}Value, onChange: (value: Value) => void) => {
  return <input value={value.getData()} onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
    onChange(value.setData(create${capitalizedAttributeType}Data(event.currentTarget.value)));
  }} />;
};`;

    throw new InvalidArgument(
      `The module you are exposing to provide a view for a data of type "${attributeType}" needs to
export a "view" property. Here is an example of a valid view es6 module for the "${attributeType}" type:
${moduleExample}`
    );
  }

  return typeConfiguration.view.view;
};

export const getDataDenormalizer = getDenormalizer(__moduleConfig as ValueDenormalizerConfig);
export const getDataView = getView(__moduleConfig as ValueDenormalizerConfig);
