import {ChannelCode, LocaleCode} from '@akeneo-pim-community/shared';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';

class InvalidArgument extends Error {}

type ViewGeneratorProps = {
  id?: string;
  value: EditionValue;
  channel: ChannelReference;
  locale: LocaleReference;
  onChange: (value: EditionValue) => void;
  onSubmit?: () => void;
  canEditData: boolean;
  invalid?: boolean;
};

type ViewGenerator = React.FC<ViewGeneratorProps>;
/**
 * @api
 */
type FilterViewProps = {
  attribute: NormalizedAttribute;
  filter: Filter | undefined;
  onFilterUpdated: (filter: Filter) => void;
  context: {
    channel: ChannelCode;
    locale: LocaleCode;
  };
};

type FilterView = React.FC<FilterViewProps>;

type ValueConfig = {
  [type: string]: {
    view: {
      view: ViewGenerator;
    };
    filter?: {
      filter: FilterView;
    };
  };
};

const hasFilterView = (config: ValueConfig, attributeType: string): boolean => {
  return undefined !== config[attributeType] && undefined !== config[attributeType].filter;
};

const getFieldView = (config: ValueConfig, value: EditionValue): ViewGenerator => {
  const attributeType = value.attribute.type;
  const typeConfiguration = config[attributeType];

  if (undefined === typeConfiguration || undefined === typeConfiguration.view) {
    const expectedConfiguration = `config:
    config:
        akeneoassetmanager/application/configuration/value:
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

const getFilterView = (config: ValueConfig, attributeType: string): FilterView => {
  const typeConfiguration = config[attributeType];
  if (undefined === typeConfiguration || undefined === typeConfiguration.filter) {
    const expectedConfiguration = `config:
    config:
        akeneoassetmanager/application/configuration/value:
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

    throw new InvalidArgument(
      `The module you are exposing to provide a view for a data of type "${attributeType}" needs to
export a "filter" property. Here is an example of a valid view es6 module for the "${attributeType}" type:
export const filter = (value: Normalized${capitalizedAttributeType}Value) => {
  return <span>{{value.getData()}}</span>;
};`
    );
  }

  return typeConfiguration.filter.filter;
};

type FilterViewCollection = {
  view: FilterView;
  attribute: NormalizedAttribute;
}[];

const getFilterViews = (config: ValueConfig, attributes: NormalizedAttribute[]): FilterViewCollection => {
  const attributesWithFilterViews = attributes.filter(({type}: NormalizedAttribute) => hasFilterView(config, type));

  return attributesWithFilterViews.map((attribute: NormalizedAttribute) => ({
    view: getFilterView(config, attribute.type),
    attribute: attribute,
  }));
};

export {
  getFieldView,
  getFilterView,
  getFilterViews,
  FilterView,
  FilterViewProps,
  ViewGenerator,
  ViewGeneratorProps,
  FilterViewCollection,
  ValueConfig,
};
