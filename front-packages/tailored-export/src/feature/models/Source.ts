import {Channel, ChannelReference, getLocalesFromChannel, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute} from './Attribute';
import {getDefaultMeasurementSource, MeasurementSource} from '../components/SourceDetails/Measurement/model';
import {
  AssetCollectionSource,
  getDefaultAssetCollectionSource,
} from '../components/SourceDetails/AssetCollection/model';
import {getDefaultTextSource, TextSource} from '../components/SourceDetails/Text/model';
import {
  getDefaultReferenceEntityCollectionSource,
  ReferenceEntityCollectionSource,
} from '../components/SourceDetails/ReferenceEntityCollection/model';
import {getDefaultFileSource, FileSource} from '../components/SourceDetails/File/model';
import {BooleanSource, getDefaultBooleanSource} from '../components/SourceDetails/Boolean/model';
import {getDefaultNumberSource, NumberSource} from '../components/SourceDetails/Number/model';
import {getDefaultIdentifierSource, IdentifierSource} from '../components/SourceDetails/Identifier/model';
import {DateSource, getDefaultDateSource} from '../components/SourceDetails/Date/model';
import {
  getDefaultPriceCollectionSource,
  PriceCollectionSource,
} from '../components/SourceDetails/PriceCollection/model';
import {getDefaultSimpleSelectSource, SimpleSelectSource} from '../components/SourceDetails/SimpleSelect/model';
import {getDefaultMultiSelectSource, MultiSelectSource} from '../components/SourceDetails/MultiSelect/model';
import {
  getDefaultReferenceEntitySource,
  ReferenceEntitySource,
} from '../components/SourceDetails/ReferenceEntity/model';
import {FamilyVariantSource, getDefaultFamilyVariantSource} from '../components/SourceDetails/FamilyVariant/model';
import {getDefaultParentSource, ParentSource} from '../components/SourceDetails/Parent/model';
import {EnabledSource, getDefaultEnabledSource} from '../components/SourceDetails/Enabled/model';
import {FamilySource, getDefaultFamilySource} from '../components/SourceDetails/Family/model';
import {getDefaultGroupsSource, GroupsSource} from '../components/SourceDetails/Groups/model';
import {CategoriesSource, getDefaultCategoriesSource} from '../components/SourceDetails/Categories/model';
import {
  getDefaultSimpleAssociationTypeSource,
  SimpleAssociationTypeSource,
} from '../components/SourceDetails/SimpleAssociationType/model';
import {AssociationType} from './AssociationType';
import {
  getDefaultQuantifiedAssociationTypeSource,
  QuantifiedAssociationTypeSource,
} from '../components/SourceDetails/QuantifiedAssociationType/model';
import {CodeSource, getDefaultCodeSource} from '../components/SourceDetails/Code/model';
import {getDefaultQualityScoreSource, QualityScoreSource} from '../components/SourceDetails/QualityScore/model';
import {getDefaultTableSource, TableSource} from '../components/SourceDetails/Table/model';

const MAX_SOURCE_COUNT = 4;

type PropertySource =
  | CategoriesSource
  | CodeSource
  | EnabledSource
  | FamilySource
  | FamilyVariantSource
  | GroupsSource
  | ParentSource
  | QualityScoreSource;

type AttributeSource =
  | AssetCollectionSource
  | BooleanSource
  | DateSource
  | FileSource
  | IdentifierSource
  | MeasurementSource
  | MultiSelectSource
  | NumberSource
  | PriceCollectionSource
  | ReferenceEntitySource
  | ReferenceEntityCollectionSource
  | SimpleSelectSource
  | TableSource
  | TextSource;

type AssociationTypeSource = SimpleAssociationTypeSource | QuantifiedAssociationTypeSource;

type Source = PropertySource | AttributeSource | AssociationTypeSource;

const getDefaultPropertySource = (sourceCode: string, channels: Channel[]): PropertySource => {
  switch (sourceCode) {
    case 'code':
      return getDefaultCodeSource();
    case 'enabled':
      return getDefaultEnabledSource();
    case 'parent':
      return getDefaultParentSource();
    case 'groups':
      return getDefaultGroupsSource();
    case 'categories':
      return getDefaultCategoriesSource();
    case 'family':
      return getDefaultFamilySource();
    case 'family_variant':
      return getDefaultFamilyVariantSource();
    case 'quality_score':
      const channel = channels[0] ?? null;
      const locale = getLocalesFromChannel(channels, channel?.code ?? null)[0] ?? null;

      if (null === channel || null === locale) {
        throw new Error('Missing channel or locale');
      }

      return getDefaultQualityScoreSource(channel.code, locale.code);
    default:
      throw new Error(`Invalid property source "${sourceCode}"`);
  }
};

const getDefaultAttributeSource = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): AttributeSource => {
  switch (attribute.type) {
    case 'pim_catalog_boolean':
      return getDefaultBooleanSource(attribute, channel, locale);
    case 'pim_catalog_date':
      return getDefaultDateSource(attribute, channel, locale);
    case 'pim_catalog_file':
    case 'pim_catalog_image':
      return getDefaultFileSource(attribute, channel, locale);
    case 'pim_catalog_identifier':
      return getDefaultIdentifierSource(attribute);
    case 'pim_catalog_metric':
      return getDefaultMeasurementSource(attribute, channel, locale);
    case 'pim_catalog_number':
      return getDefaultNumberSource(attribute, channel, locale);
    case 'pim_catalog_multiselect':
      return getDefaultMultiSelectSource(attribute, channel, locale);
    case 'pim_catalog_simpleselect':
      return getDefaultSimpleSelectSource(attribute, channel, locale);
    case 'pim_catalog_price_collection':
      return getDefaultPriceCollectionSource(attribute, channel, locale);
    case 'pim_catalog_textarea':
    case 'pim_catalog_text':
      return getDefaultTextSource(attribute, channel, locale);
    case 'pim_catalog_table':
      return getDefaultTableSource(attribute, channel, locale);
    case 'pim_catalog_asset_collection':
      return getDefaultAssetCollectionSource(attribute, channel, locale);
    case 'akeneo_reference_entity_collection':
      return getDefaultReferenceEntityCollectionSource(attribute, channel, locale);
    case 'akeneo_reference_entity':
      return getDefaultReferenceEntitySource(attribute, channel, locale);
    default:
      throw new Error(`Invalid attribute source "${attribute.type}"`);
  }
};

const getDefaultAssociationTypeSource = (associationType: AssociationType): AssociationTypeSource => {
  if (associationType.is_quantified) {
    return getDefaultQuantifiedAssociationTypeSource(associationType);
  }

  return getDefaultSimpleAssociationTypeSource(associationType);
};

export {MAX_SOURCE_COUNT, getDefaultPropertySource, getDefaultAttributeSource, getDefaultAssociationTypeSource};
export type {Source, AttributeSource, AssociationTypeSource, PropertySource};
