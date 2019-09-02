import * as React from 'react';
import {connect} from 'react-redux';
import {AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';
import {selectAttributeList, selectFamily} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/structure';
import {selectContext, ContextState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
import {selectCurrentValues, ValueCollection, Value} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';
import styled from 'styled-components';
import __ from 'akeneoreferenceentity/tools/translator';
import {Label} from 'akeneopimenrichmentassetmanager/platform/component/common/label';
import {getAttributeLabel, Attribute} from 'akeneopimenrichmentassetmanager/platform/model/structure/attribute';
import {LocaleLabel, ChannelLabel} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/context';
import {ContextLabel} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/context';
import {ThemedProps} from 'akeneopimenrichmentassetmanager/platform/component/theme';
import {Pill, Spacer, Separator} from 'akeneopimenrichmentassetmanager/platform/component/common';
import {AssetCollection} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-collection';
import {isValueComplete} from 'akeneopimenrichmentassetmanager/enrich/domain/model/product';
import {Button} from 'akeneopimenrichmentassetmanager/platform/component/common/button';
import {Family} from 'akeneopimenrichmentassetmanager/platform/model/structure/family';

type ListProps = {
  attributes: Attribute[],
  values: ValueCollection,
  family: Family|null
  context: ContextState
}

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

const List = ({values, family, context}: ListProps) => {
  return (
    <AssetCollectionList>
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
              <Button buttonSize='medium' color='outline'>{__('pim_asset_manager.asset_collection.add_asset')}</Button>
            ) : null}
          </SectionTitle>
          {/* Smart attribute indication isSmartAttribute(value.attribute.code, ruleRelations)*/}
          {/* Validation error indication hasValidationError(value.attribute.code, errors)*/}
          <AssetCollection assetFamilyIdentifier={value.attribute.referenceDataName} assetCodes={value.data} context={context} readonly={!value.editable}/>
        </AssetCollectionContainer>
      ))}
    </AssetCollectionList>
  )
};

export default connect((state: AssetCollectionState): ListProps => ({
  attributes: selectAttributeList(state),
  context: selectContext(state),
  values: selectCurrentValues(state),
  family: selectFamily(state)
}))(List);


