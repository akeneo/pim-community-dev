import React from 'react';
import {connect} from 'react-redux';
import styled from 'styled-components';
import {
  AssetsIllustration,
  Dropdown,
  IconButton,
  Information,
  Link,
  Locale as LocaleFlag,
  LockIcon,
  MoreIcon,
  Pill,
  SectionTitle,
  useBooleanState,
} from 'akeneo-design-system';
import {
  NoDataSection,
  NoDataTitle,
  useTranslate,
  LocaleCode,
  Channel,
  Locale,
  getLabel,
} from '@akeneo-pim-community/shared';
import {AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';
import {
  selectAttributeGroupList,
  selectAttributeList,
  selectChannels,
  selectFamily,
  selectLocales,
  selectRuleRelations,
} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/structure';
import {
  LabelCollection,
  ProductIdentifier,
  selectCurrentValues,
  selectProductIdentifer,
  selectProductLabels,
  updateValueData,
  Value,
  valueChanged,
  ValueCollection,
} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/product';
import {selectContext} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
import {Label} from 'akeneoassetmanager/application/component/app/label';
import {Attribute, getAttributeLabel} from 'akeneoassetmanager/platform/model/structure/attribute';
import {AssetCollection} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-collection';
import {hasValues, isValueComplete} from 'akeneopimenrichmentassetmanager/enrich/domain/model/product';
import {Family} from 'akeneoassetmanager/platform/model/structure/family';
import {RuleNotification} from 'akeneoassetmanager/platform/component/rule-notification';
import {selectErrors} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/errors';
import {ValidationError} from 'akeneoassetmanager/platform/model/validation-error';
import {ValidationErrorCollection} from 'akeneoassetmanager/platform/component/common/validation-error-collection';
import {Context} from 'akeneoassetmanager/domain/model/context';
import {AssetPicker} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker';
import {addAssetsToCollection, emptyCollection} from 'akeneoassetmanager/domain/model/asset/list-asset';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {MassUploader} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/mass-uploader';
import {
  AttributeGroupCode,
  AttributeGroupCollection,
} from 'akeneoassetmanager/platform/model/structure/attribute-group';
import {RulesNumberByAttribute} from 'akeneoassetmanager/platform/model/structure/rule-relation';
import {ReloadPreviewProvider} from 'akeneoassetmanager/application/hooks/useReloadPreview';

type DataProvider = {
  channelFetcher: {fetchAll: () => Promise<Channel[]>};
};

type ListStateProps = {
  attributes: Attribute[];
  attributeGroups: AttributeGroupCollection;
  values: ValueCollection;
  productIdentifier: ProductIdentifier | null;
  productLabels: LabelCollection;
  family: Family | null;
  context: Context;
  rulesNumberByAttribute: RulesNumberByAttribute;
  errors: ValidationError[];
  locales: Locale[];
  channels: Channel[];
};
type ListDispatchProps = {
  onChange: (value: Value) => void;
};

type DisplayValuesProps = {
  values: ValueCollection;
  attributeGroups: AttributeGroupCollection;
  productIdentifier: ProductIdentifier | null;
  productLabels: LabelCollection;
  family: Family | null;
  context: Context;
  rulesNumberByAttribute: RulesNumberByAttribute;
  errors: ValidationError[];
  dataProvider: DataProvider;
  locales: Locale[];
  channels: Channel[];
  onChange: (value: Value) => void;
};

const IncompleteIndicator = styled.div`
  display: flex;
  align-items: center;
  gap: 8px;
  margin-left: 5px;
`;

const AssetCollectionContainer = styled.div`
  display: flex;
  flex-direction: column;
  align-items: stretch;
`;

const AssetCollectionList = styled.div`
  display: flex;
  flex-direction: column;
  align-items: stretch;
`;

const getAttributeGroupLabel = (
  attributeGroups: AttributeGroupCollection,
  code: AttributeGroupCode,
  locale: LocaleCode
): string => getLabel(attributeGroups[code].labels, locale, code);

const DisplayValues = ({
  values,
  attributeGroups,
  family,
  context,
  rulesNumberByAttribute,
  onChange,
  errors,
  productIdentifier,
  productLabels,
  dataProvider,
  locales,
  channels,
}: DisplayValuesProps) => {
  const translate = useTranslate();

  return (
    <>
      {values.map(value => (
        <AssetCollectionContainer key={value.attribute.code} data-attribute={value.attribute.code}>
          <SectionTitle>
            {!value.editable && <LockIcon size={18} />}
            <SectionTitle.Title>
              {getAttributeGroupLabel(attributeGroups, value.attribute.group, context.locale)}&nbsp;/&nbsp;
              {getAttributeLabel(value.attribute, context.locale)}
            </SectionTitle.Title>
            {!isValueComplete(value, family, context.channel) && (
              <IncompleteIndicator>
                <Pill />
                <Label>{translate('pim_asset_manager.attribute.is_required')}</Label>
              </IncompleteIndicator>
            )}
            <SectionTitle.Spacer />
            <SectionTitle.Information>
              {translate('pim_asset_manager.asset_counter', {count: value.data.length}, value.data.length)}
            </SectionTitle.Information>
            {(value.channel !== null || value.locale !== null) && (
              <>
                <SectionTitle.Separator />
                {value.channel !== null &&
                  getLabel(
                    channels.find(({code}) => code === value.channel)?.labels ?? {},
                    context.locale,
                    value.channel
                  )}
                {value.locale !== null && (
                  <LocaleFlag
                    code={value.locale}
                    languageLabel={locales.find(({code}) => code === value.locale)?.label ?? `[${value.locale}]`}
                  />
                )}
              </>
            )}
            {value.editable && (
              <>
                <SectionTitle.Separator />
                <MassUploader
                  dataProvider={dataProvider}
                  assetFamilyIdentifier={value.attribute.referenceDataName}
                  context={context}
                  onAssetCreated={(assetCodes: AssetCode[]) =>
                    onChange(updateValueData(value, addAssetsToCollection(value.data, assetCodes)))
                  }
                />
                <AssetPicker
                  excludedAssetCollection={value.data}
                  assetFamilyIdentifier={value.attribute.referenceDataName}
                  initialContext={context}
                  productAttribute={value.attribute}
                  onAssetPick={(assetCodes: AssetCode[]) =>
                    onChange(updateValueData(value, addAssetsToCollection(value.data, assetCodes)))
                  }
                  productLabels={productLabels}
                  dataProvider={dataProvider}
                />
                <SecondaryActions
                  onRemoveAllAssets={() => onChange(updateValueData(value, emptyCollection(value.data)))}
                />
              </>
            )}
          </SectionTitle>
          <RuleNotification attributeCode={value.attribute.code} rulesNumberByAttribute={rulesNumberByAttribute} />
          <ValidationErrorCollection attributeCode={value.attribute.code} context={context} errors={errors} />
          <AssetCollection
            productIdentifier={productIdentifier}
            productAttribute={value.attribute}
            assetCodes={value.data}
            context={context}
            readonly={!value.editable}
            onChange={(assetCodes: AssetCode[]) => onChange(updateValueData(value, assetCodes))}
          />
        </AssetCollectionContainer>
      ))}
    </>
  );
};

type SecondaryActionsProps = {
  onRemoveAllAssets: () => void;
};

const SecondaryActions = ({onRemoveAllAssets}: SecondaryActionsProps) => {
  const translate = useTranslate();
  const [isDropdownOpen, openDropdown, closeDropdown] = useBooleanState();

  const handleItemClick = (callback: () => void) => () => {
    closeDropdown();
    callback();
  };

  return (
    <Dropdown>
      <IconButton
        title={translate('pim_common.other_actions')}
        icon={<MoreIcon />}
        level="tertiary"
        ghost="borderless"
        onClick={openDropdown}
      />
      {isDropdownOpen && (
        <Dropdown.Overlay onClose={closeDropdown}>
          <Dropdown.Header>
            <Dropdown.Title>{translate('pim_common.other_actions')}</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            <Dropdown.Item onClick={handleItemClick(onRemoveAllAssets)}>
              {translate('pim_asset_manager.asset_collection.remove_all_assets')}
            </Dropdown.Item>
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

const List = ({
  values,
  attributeGroups,
  family,
  context,
  rulesNumberByAttribute,
  errors,
  productIdentifier,
  productLabels,
  onChange,
  dataProvider,
  locales,
  channels,
}: ListStateProps &
  ListDispatchProps & {
    dataProvider: DataProvider;
  }) => {
  const translate = useTranslate();

  return (
    <ReloadPreviewProvider>
      <AssetCollectionList>
        {hasValues(values) ? (
          <DisplayValues
            values={values}
            attributeGroups={attributeGroups}
            family={family}
            context={context}
            rulesNumberByAttribute={rulesNumberByAttribute}
            onChange={onChange}
            errors={errors}
            productIdentifier={productIdentifier}
            productLabels={productLabels}
            dataProvider={dataProvider}
            locales={locales}
            channels={channels}
          />
        ) : (
          <>
            <Information
              illustration={<AssetsIllustration />}
              title={`👋  ${translate('pim_asset_manager.asset_collection.helper.title')}`}
            >
              <p>{translate('pim_asset_manager.asset_collection.helper.text')}</p>
              <Link href="https://help.akeneo.com/pim/serenity/articles/manage-your-attributes.html" target="_blank">
                {translate('pim_asset_manager.asset_collection.helper.link')}
              </Link>
            </Information>
            <NoDataSection>
              <AssetsIllustration size={256} />
              <NoDataTitle>{translate('pim_asset_manager.asset_collection.no_asset.title')}</NoDataTitle>
            </NoDataSection>
          </>
        )}
      </AssetCollectionList>
    </ReloadPreviewProvider>
  );
};

export default connect(
  (state: AssetCollectionState): ListStateProps => ({
    attributes: selectAttributeList(state),
    attributeGroups: selectAttributeGroupList(state),
    context: selectContext(state),
    values: selectCurrentValues(state),
    productIdentifier: selectProductIdentifer(state),
    productLabels: selectProductLabels(state),
    family: selectFamily(state),
    rulesNumberByAttribute: selectRuleRelations(state),
    errors: selectErrors(state),
    locales: selectLocales(state),
    channels: selectChannels(state),
  }),
  (dispatch: any): ListDispatchProps => ({
    onChange: (value: Value) => {
      dispatch(valueChanged(value));
    },
  })
)(List);
