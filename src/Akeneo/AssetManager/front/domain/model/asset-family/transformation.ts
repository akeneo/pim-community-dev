import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';

type Operation = {
  type: string;
  parameters?: {
    [key: string]: any;
  };
};

type Field = {
  attribute: AttributeIdentifier;
  channel: ChannelReference;
  locale: LocaleReference;
};

type Transformation = {
  label: string;
  source: Field;
  target: Field;
  operations: Operation[];
  filename_prefix?: string;
  filename_suffix?: string;
};

type TransformationCollection = Transformation[];

const hasFieldAsTarget = (transformations: Transformation[], field: Field) =>
  transformations.some(
    transformation =>
      transformation.target.attribute === field.attribute &&
      (transformation.target.channel === null || transformation.target.channel === field.channel) &&
      (transformation.target.locale === null || transformation.target.locale === field.locale)
  );

export {Transformation, TransformationCollection, hasFieldAsTarget};
