import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {
  DateTarget,
  getDefaultBooleanTarget,
  getDefaultDateTarget,
  getDefaultMeasurementTarget,
  getDefaultMultiReferenceEntityTarget,
  getDefaultMultiSelectTarget,
  getDefaultNumberTarget,
  getDefaultPriceTarget,
  getDefaultSimpleReferenceEntityTarget,
  getDefaultSimpleSelectTarget,
  getDefaultTextTarget,
  getDefaultAssetCollectionTarget,
  MeasurementTarget,
  MultiReferenceEntityTarget,
  MultiSelectTarget,
  NumberTarget,
  PriceTarget,
  SimpleReferenceEntityTarget,
  SimpleSelectTarget,
  TextTarget,
  AssetCollectionTarget,
} from '../components';
import {Attribute} from './Attribute';
import {AttributeDataMapping, PropertyDataMapping, DataMapping} from './DataMapping';

type TargetNotEmptyAction = 'set' | 'add';
type TargetEmptyAction = 'clear' | 'skip';

type AttributeTarget =
  | AssetCollectionTarget
  | DateTarget
  | MeasurementTarget
  | MultiReferenceEntityTarget
  | MultiSelectTarget
  | NumberTarget
  | PriceTarget
  | SimpleReferenceEntityTarget
  | SimpleSelectTarget
  | TextTarget;

type PropertyTarget = {
  code: string;
  type: 'property';
  action_if_not_empty: TargetNotEmptyAction;
  action_if_empty: TargetEmptyAction;
};

type Target = AttributeTarget | PropertyTarget;

const createAttributeTarget = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): AttributeTarget => {
  switch (attribute.type) {
    case 'pim_catalog_asset_collection':
      return getDefaultAssetCollectionTarget(attribute, channel, locale);
    case 'pim_catalog_boolean':
      return getDefaultBooleanTarget(attribute, channel, locale);
    case 'pim_catalog_date':
      return getDefaultDateTarget(attribute, channel, locale);
    case 'pim_catalog_metric':
      return getDefaultMeasurementTarget(attribute, channel, locale);
    case 'akeneo_reference_entity_collection':
      return getDefaultMultiReferenceEntityTarget(attribute, channel, locale);
    case 'pim_catalog_multiselect':
      return getDefaultMultiSelectTarget(attribute, channel, locale);
    case 'pim_catalog_number':
      return getDefaultNumberTarget(attribute, channel, locale);
    case 'pim_catalog_price_collection':
      return getDefaultPriceTarget(attribute, channel, locale);
    case 'akeneo_reference_entity':
      return getDefaultSimpleReferenceEntityTarget(attribute, channel, locale);
    case 'pim_catalog_simpleselect':
      return getDefaultSimpleSelectTarget(attribute, channel, locale);
    case 'pim_catalog_identifier':
    case 'pim_catalog_textarea':
    case 'pim_catalog_text':
      return getDefaultTextTarget(attribute, channel, locale);
    default:
      throw new Error(`Invalid attribute target "${attribute.type}"`);
  }
};

const createPropertyTarget = (code: string): PropertyTarget => ({
  code,
  type: 'property',
  action_if_not_empty: 'set',
  action_if_empty: 'skip',
});

const isAttributeTarget = (target: Target): target is AttributeTarget =>
  'attribute' === target.type && 'locale' in target && 'channel' in target;

const isAttributeDataMapping = (dataMapping: DataMapping): dataMapping is AttributeDataMapping =>
  isAttributeTarget(dataMapping.target);

const isPropertyTarget = (target: Target): target is PropertyTarget => 'property' === target.type;

const isPropertyDataMapping = (dataMapping: DataMapping): dataMapping is PropertyDataMapping =>
  isPropertyTarget(dataMapping.target);

const isTargetNotEmptyAction = (action: string): action is TargetNotEmptyAction => 'set' === action || 'add' === action;

export type {AttributeTarget, PropertyTarget, Target, TargetNotEmptyAction, TargetEmptyAction};
export {
  createAttributeTarget,
  createPropertyTarget,
  isAttributeDataMapping,
  isAttributeTarget,
  isPropertyTarget,
  isTargetNotEmptyAction,
  isPropertyDataMapping,
};
