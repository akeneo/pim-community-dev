import Value, {NormalizedValue} from 'akeneoreferenceentity/domain/model/record/value';
import ChannelReference from 'akeneoreferenceentity/domain/model/channel-reference';
import LocaleReference from 'akeneoreferenceentity/domain/model/locale-reference';
import {Attribute} from 'akeneoreferenceentity/domain/model/attribute/attribute';
import {Column, Filter} from 'akeneoreferenceentity/application/reducer/grid';

export class InvalidArgument extends Error {}

export type Denormalizer = (normalizedValue: NormalizedValue, attribute: Attribute) => Value;
export type ViewGenerator = React.SFC<{
  value: Value;
  channel: ChannelReference;
  locale: LocaleReference;
  onChange: (value: Value) => void;
  onSubmit: () => void;
  canEditData: boolean;
}>;
/**
 * @api
 */
export type CellView = React.SFC<{column: Column; value: NormalizedValue}>;
export type FilterViewProps = {
  attribute: Attribute;
  filter: Filter | undefined;
  onFilterUpdated: (filter: Filter) => void;
};

export type FilterView = React.SFC<FilterViewProps>;

type ValueConfig = {
  [type: string]: {
    denormalize: {
      denormalize: Denormalizer;
    };
    view: {
      view: ViewGenerator;
    };
    cell?: {
      cell: CellView;
    };
    filter?: {
      filter: FilterView;
    };
  };
};

export const hasCellView = (config: ValueConfig) => (attributeType: string): boolean => {
  return undefined !== config[attributeType] && undefined !== config[attributeType].cell;
};

export const hasFilterView = (config: ValueConfig) => (attributeType: string): boolean => {
  return undefined !== config[attributeType] && undefined !== config[attributeType].filter;
};

export const getDenormalizer = (config: ValueConfig) => (normalizedValue: NormalizedValue): Denormalizer => {
  const typeConfiguration = config[normalizedValue.attribute.type];

  if (undefined === typeConfiguration || undefined === typeConfiguration.denormalize) {
    const expectedConfiguration = `config:
    config:
        akeneoreferenceentity/application/configuration/value:
            ${normalizedValue.attribute.type}:
                denormalize: '@my_value_denormalizer'`;

    throw new InvalidArgument(
      `Cannot get the value denormalizer for type "${
        normalizedValue.attribute.type
      }". The configuration should look like this:
${expectedConfiguration}

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

export const getFieldView = (config: ValueConfig) => (value: Value): ViewGenerator => {
  const attributeType = value.attribute.getType();
  const typeConfiguration = config[attributeType];

  if (undefined === typeConfiguration || undefined === typeConfiguration.view) {
    const expectedConfiguration = `config:
    config:
        akeneoreferenceentity/application/configuration/value:
            ${attributeType}:
                view: '@my_data_view'`;

    throw new InvalidArgument(
      `Cannot get the data field view generator for type "${attributeType}". The configuration should look like this:
${expectedConfiguration}

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
    const expectedConfiguration = `config:
    config:
        akeneoreferenceentity/application/configuration/value:
            ${attributeType}:
                cell: '@my_data_cell_view'`;

    throw new InvalidArgument(
      `Cannot get the data cell view generator for type "${attributeType}". The configuration should look like this:
${expectedConfiguration}

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

export const getFilterView = (config: ValueConfig) => (attributeType: string): FilterView => {
  const typeConfiguration = config[attributeType];

  if (undefined === typeConfiguration || undefined === typeConfiguration.filter) {
    const expectedConfiguration = `config:
    config:
        akeneoreferenceentity/application/configuration/value:
            ${attributeType}:
                filter: '@my_data_filter_view'`;

    throw new InvalidArgument(
      `Cannot get the data filter view generator for type "${attributeType}". The configuration should look like this:
${expectedConfiguration}

Actual conf: ${JSON.stringify(config)}`
    );
  }

  if (undefined === typeConfiguration.filter.filter) {
    const capitalizedAttributeType = attributeType.charAt(0).toUpperCase() + attributeType.slice(1);
    const moduleExample = `
export const filter = (value: Normalized${capitalizedAttributeType}Value) => {
  return <span>{{value.getData()}}</span>;
};`;

    throw new InvalidArgument(
      `The module you are exposing to provide a view for a data of type "${attributeType}" needs to
export a "filter" property. Here is an example of a valid view es6 module for the "${attributeType}" type:
${moduleExample}`
    );
  }

  return typeConfiguration.filter.filter;
};

/**
 * Explanation about the __moduleConfig variable:
 * It is automatically added by a webpack loader that you can check here:
 * https://github.com/akeneo/pim-community-dev/blob/master/webpack/config-loader.js
 * This loader looks at the requirejs.yml file and find every configuration related to this module. It transform it
 * into a javascript object and add it automatically to the file on the fly.
 */
export const getDataDenormalizer = getDenormalizer(__moduleConfig as ValueConfig);
export const getDataFieldView = getFieldView(__moduleConfig as ValueConfig);
export const getDataCellView = getCellView(__moduleConfig as ValueConfig);
export const hasDataCellView = hasCellView(__moduleConfig as ValueConfig);
export const getDataFilterView = getFilterView(__moduleConfig as ValueConfig);
export const hasDataFilterView = hasFilterView(__moduleConfig as ValueConfig);
