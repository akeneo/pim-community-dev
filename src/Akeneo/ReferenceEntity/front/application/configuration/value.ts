import Value, {NormalizedValue} from 'akeneoreferenceentity/domain/model/record/value';
import Attribute from 'akeneoreferenceentity/domain/model/attribute/attribute';
import Channel from 'akeneoreferenceentity/domain/model/channel';
import AttributeIdentifier from 'akeneoreferenceentity/domain/model/attribute/identifier';
import ChannelReference from 'akeneoreferenceentity/domain/model/channel-reference';
import LocaleReference from 'akeneoreferenceentity/domain/model/locale-reference';
import Locale from 'akeneoreferenceentity/domain/model/locale';
import {Column} from 'akeneoreferenceentity/application/reducer/grid';

export class InvalidArgument extends Error {}

export type Denormalizer = (normalizedValue: NormalizedValue) => Value;
export type ViewGenerator = (value: Value) => any;
export type CellGenerator = (value: NormalizedValue) => any;

type ValueConfig = {
  [type: string]: {
    denormalize: {
      denormalize: Denormalizer;
    };
    view: {
      view: ViewGenerator;
    };
    cell: {
      cell: CellGenerator;
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

const generateKey = (attributeIdentifier: AttributeIdentifier, channel: ChannelReference, locale: LocaleReference) => {
  let key = attributeIdentifier.stringValue();
  key = !channel.isEmpty() ? `${key}_${channel.stringValue()}` : key;
  key = !locale.isEmpty() ? `${key}_${locale.stringValue()}` : key;

  return key;
};

const getColumn = (attribute: Attribute, channel: ChannelReference, locale: LocaleReference): Column => {
  if (channel.isEmpty()) {
    throw new InvalidArgument('A column cannot be generated from an empty ChannelReference');
  }

  if (locale.isEmpty()) {
    throw new InvalidArgument('A column cannot be generated from an empty LocaleReference');
  }

  return {
    key: generateKey(
      attribute.identifier,
      attribute.valuePerChannel ? channel : ChannelReference.create(null),
      attribute.valuePerLocale ? locale : LocaleReference.create(null)
    ),
    labels: attribute.getLabelCollection().normalize(),
    type: attribute.getType(),
    channel: channel.normalize() as string,
    locale: locale.normalize() as string,
  };
};

export const getColumns = (attributes: Attribute[], channels: Channel[]) => {
  return attributes
    .sort((first: Attribute, second: Attribute) => first.order - second.order)
    .reduce((columns: Column[], attribute: Attribute) => {
      channels.forEach((channel: Channel) => {
        channel.locales.forEach((locale: Locale) => {
          columns.push(
            getColumn(attribute, ChannelReference.create(channel.code), LocaleReference.create(locale.code))
          );
        });
      });

      return columns;
    }, []);
};

export const getCellView = (config: ValueConfig) => (attribute: Attribute): CellGenerator => {
  const attributeType = attribute.getType();
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
export const view = (value: Normalized${capitalizedAttributeType}Value) => {
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
