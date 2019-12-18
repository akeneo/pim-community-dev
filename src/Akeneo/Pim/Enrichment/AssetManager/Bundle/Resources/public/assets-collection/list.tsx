import * as React from 'react';
import {connect} from 'react-redux';
import {AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';
import {
  selectAttributeList,
  selectFamily,
  selectRuleRelations,
} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/structure';
import {
  AssetCode,
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
import styled from 'styled-components';
import __ from 'akeneoassetmanager/tools/translator';
import {Label} from 'akeneopimenrichmentassetmanager/platform/component/common/label';
import {Attribute, getAttributeLabel} from 'akeneopimenrichmentassetmanager/platform/model/structure/attribute';
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
import {Family} from 'akeneopimenrichmentassetmanager/platform/model/structure/family';
import AssetIllustration from 'akeneopimenrichmentassetmanager/platform/component/visual/illustration/asset';
import {
  HelperIcon,
  HelperSection,
  HelperSeparator,
  HelperText,
  HelperTitle,
} from 'akeneopimenrichmentassetmanager/platform/component/common/helper';
import {
  NoDataSection,
  NoDataText,
  NoDataTitle,
} from 'akeneopimenrichmentassetmanager/platform/component/common/no-data';
import {RuleRelation} from 'akeneopimenrichmentassetmanager/platform/model/structure/rule-relation';
import {RuleNotification} from 'akeneopimenrichmentassetmanager/platform/component/rule-notification';
import {selectErrors} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/errors';
import {ValidationError} from 'akeneopimenrichmentassetmanager/platform/model/validation-error';
import {ValidationErrorCollection} from 'akeneopimenrichmentassetmanager/platform/component/common/validation-error-collection';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import {MoreButton} from 'akeneoassetmanager/application/component/app/more-button';
import {
  addAssetsToCollection,
  emptyCollection,
} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {AssetPicker} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker';
import LockIcon from 'akeneopimenrichmentassetmanager/platform/component/visual/icon/lock';
import {ResultCounter} from 'akeneopimenrichmentassetmanager/platform/component/common/result-counter';

type ListStateProps = {
  attributes: Attribute[];
  values: ValueCollection;
  productIdentifier: ProductIdentifier | null;
  productLabels: LabelCollection;
  family: Family | null;
  context: Context;
  ruleRelations: RuleRelation[];
  errors: ValidationError[];
};
type ListDispatchProps = {
  onChange: (value: Value) => void;
};

type DisplayValuesProps = {
  values: ValueCollection;
  productIdentifier: ProductIdentifier | null;
  productLabels: LabelCollection;
  family: Family | null;
  context: Context;
  ruleRelations: RuleRelation[];
  errors: ValidationError[];
  onChange: (value: Value) => void;
};

const SectionTitle = styled.div`
  display: flex;
  padding: 12px 0;
  align-items: center; //Should be baseline but the alignment is then very weird
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

const DisplayValues = ({
  values,
  family,
  context,
  ruleRelations,
  onChange,
  errors,
  productIdentifier,
  productLabels,
}: DisplayValuesProps) => {
  return (
    <React.Fragment>
      {values.map((value: Value) => (
        <AssetCollectionContainer key={value.attribute.code} data-attribute={value.attribute.code}>
          <SectionTitle>
            {!value.editable ? (
              <LockIconContainer>
                <LockIcon />
              </LockIconContainer>
            ) : null}
            <AttributeBreadCrumb readonly={!value.editable}>
              {value.attribute.group} / {getAttributeLabel(value.attribute, context.locale)}
            </AttributeBreadCrumb>
            {!isValueComplete(value, family, context.channel) ? (
              <IncompleteIndicator>
                <Pill />
                <Label>{__('pim_asset_manager.attribute.is_required')}</Label>
              </IncompleteIndicator>
            ) : null}
            <Spacer />
            <ResultCounter count={value.data.length} labelKey={'pim_asset_manager.asset_collection.asset_counter'} />
            <Separator />
            {value.channel !== null || value.locale !== null ? (
              <React.Fragment>
                <ContextLabel>
                  {value.channel !== null ? <ChannelLabel channelCode={value.channel} /> : null}
                  {value.locale !== null ? <LocaleLabel localeCode={value.locale} /> : null}
                </ContextLabel>
                <Separator />
              </React.Fragment>
            ) : null}
            {value.editable ? (
              <React.Fragment>
                <AssetPicker
                  excludedAssetCollection={value.data}
                  assetFamilyIdentifier={value.attribute.referenceDataName}
                  initialContext={context}
                  productAttribute={value.attribute}
                  onAssetPick={(assetCodes: AssetCode[]) => {
                    onChange(updateValueData(value, addAssetsToCollection(value.data, assetCodes)));
                  }}
                  productLabels={productLabels}
                />
                <MoreButton
                  elements={[
                    {
                      label: __('pim_asset_manager.asset_collection.remove_all_assets'),
                      action: () => {
                        onChange(updateValueData(value, emptyCollection(value.data)));
                      },
                    },
                  ]}
                />
              </React.Fragment>
            ) : null}
          </SectionTitle>
          <RuleNotification attributeCode={value.attribute.code} ruleRelations={ruleRelations} />
          <ValidationErrorCollection attributeCode={value.attribute.code} context={context} errors={errors} />
          <AssetCollection
            productIdentifier={productIdentifier}
            productAttribute={value.attribute}
            assetCodes={value.data}
            context={context}
            readonly={!value.editable}
            onChange={(assetCodes: AssetCode[]) => {
              onChange(updateValueData(value, assetCodes));
            }}
          />
        </AssetCollectionContainer>
      ))}
    </React.Fragment>
  );
};

const List = ({
  values,
  family,
  context,
  ruleRelations,
  errors,
  productIdentifier,
  productLabels,
  onChange,
}: ListStateProps & ListDispatchProps) => {
  const familyLabel = null !== family ? family.labels[context.locale] : '';

  return (
    <AssetCollectionList>
      {hasValues(values) ? (
        <DisplayValues
          values={values}
          family={family}
          context={context}
          ruleRelations={ruleRelations}
          onChange={onChange}
          errors={errors}
          productIdentifier={productIdentifier}
          productLabels={productLabels}
        />
      ) : (
        <React.Fragment>
          <HelperSection>
            <HelperIcon src="/bundles/pimui/images/illustrations/Asset.svg" />
            <HelperSeparator />
            <HelperTitle>
              👋 {__('pim_asset_manager.asset_collection.helper.title')}
              <HelperText>
                {__('pim_asset_manager.asset_collection.helper.text', {family: familyLabel})}
                <br />
                <a href="#">{__('pim_asset_manager.asset_collection.helper.link')}</a>
              </HelperText>
            </HelperTitle>
          </HelperSection>
          <NoDataSection>
            <AssetIllustration size={256} />
            <NoDataTitle>{__('pim_asset_manager.asset_collection.no_asset.title')}</NoDataTitle>
            <NoDataText>
              {__('pim_asset_manager.asset_collection.no_asset.text', {family: familyLabel})}
              <Spacer />
              <a href="#">{__('pim_asset_manager.asset_collection.helper.link')}</a>
            </NoDataText>
          </NoDataSection>
        </React.Fragment>
      )}
    </AssetCollectionList>
  );
};

export default connect(
  (state: AssetCollectionState): ListStateProps => ({
    attributes: selectAttributeList(state),
    context: selectContext(state),
    values: selectCurrentValues(state),
    productIdentifier: selectProductIdentifer(state),
    productLabels: selectProductLabels(state),
    family: selectFamily(state),
    ruleRelations: selectRuleRelations(state),
    errors: selectErrors(state),
  }),
  (dispatch: any): ListDispatchProps => ({
    onChange: (value: Value) => {
      dispatch(valueChanged(value));
    },
  })
)(List);
