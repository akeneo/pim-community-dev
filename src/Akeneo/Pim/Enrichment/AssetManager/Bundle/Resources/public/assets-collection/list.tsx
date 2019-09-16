import * as React from 'react';
import {connect} from 'react-redux';
import {AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';
import {selectAttributeList, selectFamily, selectRuleRelations} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/structure';
import {selectCurrentValues, ValueCollection, Value, AssetCode, updateValueData, valueChanged} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';
import {selectContext} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
import styled from 'styled-components';
import __ from 'akeneoassetmanager/tools/translator';
import {Label} from 'akeneopimenrichmentassetmanager/platform/component/common/label';
import {getAttributeLabel, Attribute} from 'akeneopimenrichmentassetmanager/platform/model/structure/attribute';
import {LocaleLabel, ChannelLabel} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/context';
import {ContextLabel} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/context';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {Pill, Spacer, Separator} from 'akeneopimenrichmentassetmanager/platform/component/common';
import {AssetCollection} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-collection';
import {isValueComplete, hasValues} from 'akeneopimenrichmentassetmanager/enrich/domain/model/product';
import {Button} from 'akeneopimenrichmentassetmanager/platform/component/common/button';
import {Family} from 'akeneopimenrichmentassetmanager/platform/model/structure/family';
import AssetIllustration from 'akeneopimenrichmentassetmanager/platform/component/visual/illustration/asset';
import {HelperSection, HelperIcon, HelperSeparator, HelperTitle, HelperText} from 'akeneopimenrichmentassetmanager/platform/component/common/helper';
import {NoDataSection, NoDataTitle, NoDataText} from 'akeneopimenrichmentassetmanager/platform/component/common/no-data';
import {RuleRelation} from 'akeneopimenrichmentassetmanager/platform/model/structure/rule-relation';
import {RuleNotification} from 'akeneopimenrichmentassetmanager/platform/component/rule-notification';
import {selectErrors} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/errors';
import {ValidationError} from 'akeneopimenrichmentassetmanager/platform/model/validation-error';
import {ValidationErrorCollection} from 'akeneopimenrichmentassetmanager/platform/component/common/validation-error-collection';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import {MoreButton} from 'akeneoassetmanager/application/component/app/more-button';
import {emptyCollection} from 'akeneopimenrichmentassetmanager/assets-collection/domain/model/asset';
import {AssetPicker} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker';

type ListStateProps = {
  attributes: Attribute[],
  values: ValueCollection,
  family: Family|null,
  context: Context,
  ruleRelations: RuleRelation[],
  errors: ValidationError[]
}
type ListDispatchProps = {
  onChange: (value: Value) => void
}

type DisplayValuesProps = {
  values: ValueCollection,
  family: Family|null,
  context: Context
  ruleRelations: RuleRelation[]
  errors: ValidationError[]
  onChange: (value: Value) => void
};

const SectionTitle = styled.div`
  display: flex;
  padding: 12px 0;
  align-items: center; //Should be baseline but the alignment is then very weird
  border-bottom: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey140}
`;

const AttributeBreadCrumb = styled.div`
  font-size: 15px;
  font-weight: normal;
  text-transform: uppercase;
  white-space: nowrap;
  color: ${(props: ThemedProps<void>) => props.theme.color.grey140}
`;

const IncompleteIndicator = styled.div`
  display: flex;
`;

const AssetCounter = styled.div`
  white-space: nowrap;
  color: ${(props: ThemedProps<void>) => props.theme.color.purple100};
  margin-left: 10px;
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


const DisplayValues = ({values, family, context, ruleRelations, onChange, errors}: DisplayValuesProps) => {
  const [assetPicker, toggleAssetPicker] = React.useState(false);
  return (
    <React.Fragment>
      {values.map((value: Value) => (
        <AssetCollectionContainer key={value.attribute.code}>
          <SectionTitle>
            <AttributeBreadCrumb>
              {value.attribute.group} / {getAttributeLabel(value.attribute, context.locale)}
            </AttributeBreadCrumb>
            {!isValueComplete(value, family, context.channel) ? (
              <IncompleteIndicator>
                <Pill />
                <Label small grey>{__('pim_asset_manager.attribute.is_required')}</Label>
              </IncompleteIndicator>
            ) : null}
            <Spacer />
            <AssetCounter>
              {__('pim_asset_manager.asset_collection.asset_count', {count: value.data.length}, value.data.length)}
            </AssetCounter>
            <Separator />
            {value.channel !== null || value.locale !== null ? (
              <React.Fragment>
                <ContextLabel>
                  {value.channel !== null ? <ChannelLabel channelCode={value.channel}/> : null}
                  {value.locale !== null ? <LocaleLabel localeCode={value.locale}/> : null}
                </ContextLabel>
                <Separator />
              </React.Fragment>
            ): null}
            {value.editable ? (
              <React.Fragment>
                <Button buttonSize='medium' color='outline' onClick={() => toggleAssetPicker(true)}>{__('pim_asset_manager.asset_collection.add_asset')}</Button>
                <AssetPicker show={assetPicker} onClose={() => toggleAssetPicker(false)} />
                <MoreButton elements={[{
                  label: __('pim_asset_manager.asset_collection.remove_all_assets'),
                  action: () => {
                    onChange(updateValueData(value, emptyCollection(value.data)))
                  }
                }]}/>
              </React.Fragment>
            ) : null}
          </SectionTitle>
          <RuleNotification attributeCode={value.attribute.code} ruleRelations={ruleRelations} />
          <ValidationErrorCollection attributeCode={value.attribute.code} context={context} errors={errors} />
          <AssetCollection assetFamilyIdentifier={value.attribute.referenceDataName} assetCodes={value.data} context={context} readonly={!value.editable} onChange={(assetCodes: AssetCode[]) => {
            onChange(updateValueData(value, assetCodes))
          }}/>
        </AssetCollectionContainer>
      ))}
    </React.Fragment>
  );
};

const List = ({values, family, context, ruleRelations, errors, onChange}: ListStateProps & ListDispatchProps) => {
  const familyLabel = (null !== family) ? family.labels[context.locale] : '';

  return (
    <AssetCollectionList>
      {hasValues(values) ? (
        <DisplayValues values={values} family={family} context={context} ruleRelations={ruleRelations} onChange={onChange} errors={errors}/>
      ) : (
        <React.Fragment>
          <HelperSection>
            <HelperIcon src='/bundles/pimui/images/illustrations/Asset.svg' />
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
            <AssetIllustration size={256}/>
            <NoDataTitle>
              {__('pim_asset_manager.asset_collection.no_asset.title')}
            </NoDataTitle>
            <NoDataText>
              {__('pim_asset_manager.asset_collection.no_asset.text', {family: familyLabel})}
              <Spacer />
              <a href="#">{__('pim_asset_manager.asset_collection.helper.link')}</a>
            </NoDataText>
          </NoDataSection>
        </React.Fragment>
      )}
    </AssetCollectionList>
  )
};

export default connect((state: AssetCollectionState): ListStateProps => ({
  attributes: selectAttributeList(state),
  context: selectContext(state),
  values: selectCurrentValues(state),
  family: selectFamily(state),
  ruleRelations: selectRuleRelations(state),
  errors: selectErrors(state)
}), (dispatch: any): ListDispatchProps => ({
  onChange: (value: Value) => {
    dispatch(valueChanged(value))
  }
}))(List);
