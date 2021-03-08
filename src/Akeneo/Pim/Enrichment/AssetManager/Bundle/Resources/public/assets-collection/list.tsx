import React from 'react';
import {connect} from 'react-redux';
import styled from 'styled-components';
import __ from 'akeneoassetmanager/tools/translator';
import {AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';
import {
  selectAttributeGroupList,
  selectAttributeList,
  selectFamily,
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
import {
  ChannelLabel,
  ContextLabel,
  LocaleLabel,
} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/context';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {Pill} from 'akeneoassetmanager/application/component/app/pill';
import {Separator} from 'akeneoassetmanager/application/component/app/separator';
import {Spacer} from 'akeneoassetmanager/application/component/app/spacer';
import {AssetCollection} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-collection';
import {hasValues, isValueComplete} from 'akeneopimenrichmentassetmanager/enrich/domain/model/product';
import {Family} from 'akeneoassetmanager/platform/model/structure/family';
import {NoDataSection, NoDataTitle} from 'akeneoassetmanager/platform/component/common/no-data';
import {RuleNotification} from 'akeneoassetmanager/platform/component/rule-notification';
import {selectErrors} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/errors';
import {ValidationError} from 'akeneoassetmanager/platform/model/validation-error';
import {ValidationErrorCollection} from 'akeneoassetmanager/platform/component/common/validation-error-collection';
import {Context} from 'akeneoassetmanager/domain/model/context';
import {MoreButton} from 'akeneoassetmanager/application/component/app/more-button';
import {AssetPicker} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker';
import {ResultCounter} from 'akeneoassetmanager/application/component/app/result-counter';
import {addAssetsToCollection, emptyCollection} from 'akeneoassetmanager/domain/model/asset/list-asset';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import fetchAllChannels from 'akeneoassetmanager/infrastructure/fetcher/channel';
import assetFamilyFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset-family';
import {MassUploader} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/mass-uploader';
import {getLabelInCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {
  AttributeGroupCode,
  AttributeGroupCollection,
} from 'akeneoassetmanager/platform/model/structure/attribute-group';
import {AssetsIllustration, Information, Link, LockIcon} from 'akeneo-design-system';
import {ButtonContainer} from 'akeneoassetmanager/application/component/app/button';
import {RulesNumberByAttribute} from 'akeneoassetmanager/platform/model/structure/rule-relation';

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
  onChange: (value: Value) => void;
};

const SectionTitle = styled.div`
  display: flex;
  padding: 12px 0;
  align-items: center; /* Should be baseline but the alignment is then very weird */
  border-bottom: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey140};
`;

const AttributeBreadCrumb = styled.div<{readonly: boolean}>`
  font-size: 15px;
  font-weight: normal;
  text-transform: uppercase;
  white-space: nowrap;
  color: ${(props: ThemedProps<{readonly: boolean}>) =>
    props.readonly ? props.theme.color.grey100 : props.theme.color.grey140};
`;

const IncompleteIndicator = styled.div`
  display: flex;
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

const LockIconContainer = styled.div`
  margin-right: 5px;
  height: 14px;
`;

const dataProvider = {
  assetFamilyFetcher,
  channelFetcher: {fetchAll: fetchAllChannels},
};

const getAttributeGroupLabel = (
  attributeGroups: AttributeGroupCollection,
  code: AttributeGroupCode,
  locale: LocaleCode
): string => getLabelInCollection(attributeGroups[code].labels, locale, true, code);

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
}: DisplayValuesProps) => (
  <>
    {values.map((value: Value) => {
      const assetCollectionTitle = `${getAttributeGroupLabel(attributeGroups, value.attribute.group, context.locale)} /
      ${getAttributeLabel(value.attribute, context.locale)}`;

      return (
        <AssetCollectionContainer key={value.attribute.code} data-attribute={value.attribute.code}>
          <SectionTitle>
            {!value.editable && (
              <LockIconContainer>
                <LockIcon size={14} />
              </LockIconContainer>
            )}
            <AttributeBreadCrumb readonly={!value.editable}>{assetCollectionTitle}</AttributeBreadCrumb>
            {!isValueComplete(value, family, context.channel) && (
              <IncompleteIndicator>
                <Pill />
                <Label>{__('pim_asset_manager.attribute.is_required')}</Label>
              </IncompleteIndicator>
            )}
            <Spacer />
            <ResultCounter count={value.data.length} labelKey={'pim_asset_manager.asset_counter'} />
            <Separator />
            {(value.channel !== null || value.locale !== null) && (
              <>
                <ContextLabel>
                  {value.channel !== null && <ChannelLabel channelCode={value.channel} />}
                  {value.locale !== null && <LocaleLabel localeCode={value.locale} />}
                </ContextLabel>
                <Separator />
              </>
            )}
            {value.editable && (
              <ButtonContainer>
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
                />
                <MoreButton
                  elements={[
                    {
                      label: __('pim_asset_manager.asset_collection.remove_all_assets'),
                      action: () => onChange(updateValueData(value, emptyCollection(value.data))),
                    },
                  ]}
                />
              </ButtonContainer>
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
      );
    })}
  </>
);

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
}: ListStateProps & ListDispatchProps) => (
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
      />
    ) : (
      <>
        <Information
          illustration={<AssetsIllustration />}
          title={`👋  ${__('pim_asset_manager.asset_collection.helper.title')}`}
        >
          <p>{__('pim_asset_manager.asset_collection.helper.text')}</p>
          <Link href="https://help.akeneo.com/pim/v4/articles/manage-your-attributes.html" target="_blank">
            {__('pim_asset_manager.asset_collection.helper.link')}
          </Link>
        </Information>
        <NoDataSection>
          <AssetsIllustration size={256} />
          <NoDataTitle>{__('pim_asset_manager.asset_collection.no_asset.title')}</NoDataTitle>
        </NoDataSection>
      </>
    )}
  </AssetCollectionList>
);

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
  }),
  (dispatch: any): ListDispatchProps => ({
    onChange: (value: Value) => {
      dispatch(valueChanged(value));
    },
  })
)(List);
