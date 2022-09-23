import {Channel} from '@akeneo-pim-community/shared';
import {Attribute} from './pim/Attribute';
import {getDefaultAssociationTypeSource, getDefaultPropertySource, getDefaultAttributeSource} from './Source';
import {getDefaultAssetCollectionSource} from '../components/SourceDetails/AssetCollection/model';
import {getDefaultBooleanSource} from '../components/SourceDetails/Boolean/model';
import {getDefaultCategoriesSource} from '../components/SourceDetails/Categories/model';
import {getDefaultDateSource} from '../components/SourceDetails/Date/model';
import {getDefaultEnabledSource} from '../components/SourceDetails/Enabled/model';
import {getDefaultFamilySource} from '../components/SourceDetails/Family/model';
import {getDefaultFamilyVariantSource} from '../components/SourceDetails/FamilyVariant/model';
import {getDefaultFileSource} from '../components/SourceDetails/File/model';
import {getDefaultGroupsSource} from '../components/SourceDetails/Groups/model';
import {getDefaultIdentifierSource} from '../components/SourceDetails/Identifier/model';
import {getDefaultMeasurementSource} from '../components/SourceDetails/Measurement/model';
import {getDefaultMultiSelectSource} from '../components/SourceDetails/MultiSelect/model';
import {getDefaultNumberSource} from '../components/SourceDetails/Number/model';
import {getDefaultParentSource} from '../components/SourceDetails/Parent/model';
import {getDefaultPriceCollectionSource} from '../components/SourceDetails/PriceCollection/model';
import {getDefaultReferenceEntityCollectionSource} from '../components/SourceDetails/ReferenceEntityCollection/model';
import {getDefaultReferenceEntitySource} from '../components/SourceDetails/ReferenceEntity/model';
import {getDefaultSimpleSelectSource} from '../components/SourceDetails/SimpleSelect/model';
import {getDefaultTextSource} from '../components/SourceDetails/Text/model';
import {getDefaultSimpleAssociationTypeSource} from '../components/SourceDetails/SimpleAssociationType/model';
import {getDefaultQuantifiedAssociationTypeSource} from '../components/SourceDetails/QuantifiedAssociationType/model';
import {getDefaultCodeSource} from '../components/SourceDetails/Code/model';
import {getDefaultQualityScoreSource} from '../components/SourceDetails/QualityScore/model';
import {getDefaultTableSource} from '../components/SourceDetails/Table/model';

const channels: Channel[] = [
  {
    code: 'ecommerce',
    labels: {fr_FR: 'Ecommerce'},
    locales: [
      {
        code: 'en_US',
        label: 'English (United States)',
        region: 'US',
        language: 'en',
      },
    ],
    category_tree: '',
    conversion_units: [],
    currencies: [],
    meta: {
      created: '',
      form: '',
      id: 1,
      updated: '',
    },
  },
];

const getAttribute = (type: string): Attribute => ({
  code: 'nice_attribute',
  type,
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
});

jest.mock('akeneo-design-system/lib/shared/uuid', () => ({
  uuid: () => '276b6361-badb-48a1-98ef-d75baa235148',
}));

test('it can get the default property source by property name', () => {
  const target = {
    type: 'string',
    name: 'a_target',
    required: false,
  } as const;
  expect(getDefaultPropertySource('enabled', target, channels)).toEqual(getDefaultEnabledSource());
  expect(getDefaultPropertySource('parent', target, channels)).toEqual(getDefaultParentSource());
  expect(getDefaultPropertySource('groups', target, channels)).toEqual(getDefaultGroupsSource());
  expect(getDefaultPropertySource('code', target, channels)).toEqual(getDefaultCodeSource());
  expect(getDefaultPropertySource('categories', target, channels)).toEqual(getDefaultCategoriesSource());
  expect(getDefaultPropertySource('family', target, channels)).toEqual(getDefaultFamilySource());
  expect(getDefaultPropertySource('family_variant', target, channels)).toEqual(getDefaultFamilyVariantSource());
  expect(getDefaultPropertySource('quality_score', target, channels)).toEqual(
    getDefaultQualityScoreSource('ecommerce', 'en_US')
  );
  expect(() => getDefaultPropertySource('unknown', target, channels)).toThrowError('Invalid property source "unknown"');
  expect(() => getDefaultPropertySource('quality_score', target, [])).toThrowError('Missing channel or locale');
});

test('it can get the default attribute source by attribute type', () => {
  const target = {
    type: 'string',
    name: 'a_target',
    required: false,
  } as const;

  expect(getDefaultAttributeSource(getAttribute('pim_catalog_boolean'), target, 'ecommerce', 'br_FR')).toEqual(
    getDefaultBooleanSource(getAttribute('pim_catalog_boolean'), 'ecommerce', 'br_FR')
  );
  expect(getDefaultAttributeSource(getAttribute('pim_catalog_date'), target, 'ecommerce', 'br_FR')).toEqual(
    getDefaultDateSource(getAttribute('pim_catalog_date'), 'ecommerce', 'br_FR')
  );
  expect(getDefaultAttributeSource(getAttribute('pim_catalog_file'), target, 'ecommerce', 'br_FR')).toEqual(
    getDefaultFileSource(getAttribute('pim_catalog_file'), 'ecommerce', 'br_FR')
  );
  expect(getDefaultAttributeSource(getAttribute('pim_catalog_image'), target, 'ecommerce', 'br_FR')).toEqual(
    getDefaultFileSource(getAttribute('pim_catalog_image'), 'ecommerce', 'br_FR')
  );
  expect(getDefaultAttributeSource(getAttribute('pim_catalog_identifier'), target, 'ecommerce', 'br_FR')).toEqual(
    getDefaultIdentifierSource(getAttribute('pim_catalog_identifier'))
  );
  expect(getDefaultAttributeSource(getAttribute('pim_catalog_metric'), target, 'ecommerce', 'br_FR')).toEqual(
    getDefaultMeasurementSource(getAttribute('pim_catalog_metric'), target, 'ecommerce', 'br_FR')
  );
  expect(getDefaultAttributeSource(getAttribute('pim_catalog_number'), target, 'ecommerce', 'br_FR')).toEqual(
    getDefaultNumberSource(getAttribute('pim_catalog_number'), 'ecommerce', 'br_FR')
  );
  expect(getDefaultAttributeSource(getAttribute('pim_catalog_multiselect'), target, 'ecommerce', 'br_FR')).toEqual(
    getDefaultMultiSelectSource(getAttribute('pim_catalog_multiselect'), 'ecommerce', 'br_FR')
  );
  expect(getDefaultAttributeSource(getAttribute('pim_catalog_simpleselect'), target, 'ecommerce', 'br_FR')).toEqual(
    getDefaultSimpleSelectSource(getAttribute('pim_catalog_simpleselect'), 'ecommerce', 'br_FR')
  );
  expect(getDefaultAttributeSource(getAttribute('pim_catalog_price_collection'), target, 'ecommerce', 'br_FR')).toEqual(
    getDefaultPriceCollectionSource(
      getAttribute('pim_catalog_price_collection'),
      {type: 'string', name: 'item_name', required: false},
      'ecommerce',
      'br_FR'
    )
  );
  expect(getDefaultAttributeSource(getAttribute('pim_catalog_textarea'), target, 'ecommerce', 'br_FR')).toEqual(
    getDefaultTextSource(getAttribute('pim_catalog_textarea'), 'ecommerce', 'br_FR')
  );
  expect(getDefaultAttributeSource(getAttribute('pim_catalog_text'), target, 'ecommerce', 'br_FR')).toEqual(
    getDefaultTextSource(getAttribute('pim_catalog_text'), 'ecommerce', 'br_FR')
  );
  expect(getDefaultAttributeSource(getAttribute('pim_catalog_asset_collection'), target, 'ecommerce', 'br_FR')).toEqual(
    getDefaultAssetCollectionSource(
      getAttribute('pim_catalog_asset_collection'),
      {type: 'string', name: 'item_name', required: false},
      'ecommerce',
      'br_FR'
    )
  );
  expect(
    getDefaultAttributeSource(getAttribute('akeneo_reference_entity_collection'), target, 'ecommerce', 'br_FR')
  ).toEqual(
    getDefaultReferenceEntityCollectionSource(getAttribute('akeneo_reference_entity_collection'), 'ecommerce', 'br_FR')
  );
  expect(getDefaultAttributeSource(getAttribute('akeneo_reference_entity'), target, 'ecommerce', 'br_FR')).toEqual(
    getDefaultReferenceEntitySource(getAttribute('akeneo_reference_entity'), 'ecommerce', 'br_FR')
  );
  expect(getDefaultAttributeSource(getAttribute('pim_catalog_table'), target, 'ecommerce', 'br_FR')).toEqual(
    getDefaultTableSource(getAttribute('pim_catalog_table'), 'ecommerce', 'br_FR')
  );
  expect(() => getDefaultAttributeSource(getAttribute('unknown_type'), target, 'ecommerce', 'br_FR')).toThrowError();
});

test('it can get the default association type source by type', () => {
  expect(getDefaultAssociationTypeSource({code: 'UPSELL', labels: {}, is_quantified: false})).toEqual(
    getDefaultSimpleAssociationTypeSource({code: 'UPSELL', labels: {}, is_quantified: false})
  );
  expect(getDefaultAssociationTypeSource({code: 'PACK', labels: {}, is_quantified: true})).toEqual(
    getDefaultQuantifiedAssociationTypeSource({code: 'PACK', labels: {}, is_quantified: true})
  );
});
