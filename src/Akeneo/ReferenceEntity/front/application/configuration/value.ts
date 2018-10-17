import Value, {NormalizedValue} from 'akeneoreferenceentity/domain/model/record/value';

export class InvalidArgument extends Error {}

export type Denormalizer = (normalizedValue: NormalizedValue) => Value;
export type ViewGenerator = React.SFC<{value: Value; onChange: (value: Value) => void; onSubmit: () => void}>;
export type CellView = React.SFC<{value: NormalizedValue}>;

type ValueConfig = {
  [type: string]: {
    denormalize: {
      denormalizeData: Denormalizer;
    };
    view: {
      view: ViewGenerator;
    };
    cell: {
      cell: CellView;
    };
  };
};

export const getDenormalizer = (config: ValueConfig) => (normalizedValue: NormalizedValue): Denormalizer => {
  const typeConfiguration = config[normalizedValue.attribute.type];

  if (undefined === typeConfiguration || undefined === typeConfiguration.denormalize) {
    const confPath = `config:
    config:
        akeneoreferenceentity/application/configuration/value:
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

  if (undefined === typeConfiguration.denormalize.denormalizeData) {
    const moduleExample = `
export const denormalizeData = (normalizedBooleanData: boolean) => {
  return new BooleanData(normalizedBooleanData);
};
`;

    throw new InvalidArgument(
      `The module you are exposing to denormalize a value of type "${normalizedValue.attribute.type}" needs to
export a "denormalizeData" property. Here is an example of a valid denormalize es6 module:
${moduleExample}`
    );
  }

  return typeConfiguration.denormalize.denormalizeData;
};

export const getFieldView = (config: ValueConfig) => (value: Value): ViewGenerator => {
  const attributeType = value.attribute.getType();
  const typeConfiguration = config[attributeType];

  if (undefined === typeConfiguration || undefined === typeConfiguration.view) {
    const confPath = `config:
    config:
        akeneoreferenceentity/application/configuration/value:
            ${attributeType}:
                view: '@my_data_view'`;

    throw new InvalidArgument(
      `Cannot get the data field view generator for type "${attributeType}". The configuration should look like this:
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

export const getCellView = (config: ValueConfig) => (attributeType: string): CellView => {
  const typeConfiguration = config[attributeType];

  if (undefined === typeConfiguration || undefined === typeConfiguration.cell) {
    const confPath = `config:
    config:
        akeneoreferenceentity/application/configuration/value:
            ${attributeType}:
                cell: '@my_data_cell_view'`;

    throw new InvalidArgument(
      `Cannot get the data cell view generator for type "${attributeType}". The configuration should look like this:
${confPath}

Actual conf: ${JSON.stringify(config)}`
    );
  }

  if (undefined === typeConfiguration.cell.cell) {
    const capitalizedAttributeType = attributeType.charAt(0).toUpperCase() + attributeType.slice(1);
    const moduleExample = `
export const cell = (value: Normalized${capitalizedAttributeType}Value) => {
  return <span>{{value.getData()}}</span>;
};`;

    throw new InvalidArgument(
      `The module you are exposing to provide a view for a data of type "${attributeType}" needs to
export a "cell" property. Here is an example of a valid view es6 module for the "${attributeType}" type:
${moduleExample}`
    );
  }

  return typeConfiguration.cell.cell;
};

export const getDataDenormalizer = getDenormalizer(__moduleConfig as ValueConfig);
export const getDataFieldView = getFieldView(__moduleConfig as ValueConfig);
export const getDataCellView = getCellView(__moduleConfig as ValueConfig);
