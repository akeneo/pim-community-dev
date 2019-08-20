import * as React from 'react';
import {connect} from 'react-redux';
import {AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';
import {selectAttributeList} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/structure';
import {selectContext, ContextState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
import {ValueCollection, Value, selectCurrentValues} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';
import styled from 'styled-components';
import __ from 'akeneoreferenceentity/tools/translator';
import {Label} from 'akeneopimenrichmentassetmanager/platform/component/common/label';
import {getAttributeLabel, Attribute} from 'akeneopimenrichmentassetmanager/platform/model/structure/attribute';
import {ChannelLabel} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/channel';
import {LocaleLabel} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/locale';
import {ContextLabel} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/context';
import {ThemedProps} from 'akeneopimenrichmentassetmanager/platform/component/theme';
import {Pill, Spacer, Separator} from 'akeneopimenrichmentassetmanager/platform/component/common';
import {AssetCollection} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-collection';

type ListProps = {
  attributes: Attribute[],
  values: ValueCollection,
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
  color: ${(props: ThemedProps<void>) => props.theme.color.grey140}
`;

const IncompleteIndicator = styled.div`
  display: flex;
`;

const AssetCounter = styled.div`
  color: ${(props: ThemedProps<void>) => props.theme.color.purple100};
`;

type ButtonProps = {
  buttonSize: 'micro' | 'medium' | 'default',
  color: 'green' | 'blue' | 'red' | 'grey' | 'outline'
}

const Button = styled.div`
  padding: 0 ${(props: ThemedProps<ButtonProps>) => 'micro' === props.buttonSize ? '10xp' : '15px'};
  height: ${(props: ThemedProps<ButtonProps>) => {
    switch (props.buttonSize) {
      case 'micro':
        return '20px';
      case 'medium':
        return '24px';
      default:
        return '32px';
    }
  }};
  line-height: ${(props: ThemedProps<ButtonProps>) => 'micro' === props.buttonSize ? '19xp' : '23px'};
  border-radius: ${(props: ThemedProps<ButtonProps>) => 'micro' === props.buttonSize ? '10xp' : '16px'};
  font-size: ${(props: ThemedProps<ButtonProps>) => 'micro' === props.buttonSize ? props.theme.fontSize.small : props.theme.fontSize.default};
  minimum-width: ${(props: ThemedProps<ButtonProps>) => 'micro' === props.buttonSize ? '60px' : '100px'};
  color: ${(props: ThemedProps<ButtonProps>) => 'outline' !== props.color ? 'white' : props.theme.color.grey120};
  background-color: ${(props: ThemedProps<ButtonProps>) => 'outline' !== props.color ? (props.theme.color as any)[props.color + '100'] : 'white'};
  cursor: pointer;
  text-transform: uppercase;
  border: 1px solid ${(props: ThemedProps<ButtonProps>) => 'outline' !== props.color ? 'transparent' : props.theme.color.grey80};
`;



const List = ({values, context}: ListProps) => {
  return (
    <React.Fragment>
      {values.map((value: Value) => (
        <React.Fragment key={value.attribute.code}>
          <SectionTitle>
            <AttributeBreadCrumb>
              {value.attribute.group} / {getAttributeLabel(value.attribute, context.locale)}
            </AttributeBreadCrumb>
            <IncompleteIndicator>
              <Pill />
              <Label small grey>{__('pim_asset_manager.attribute.is_required')}</Label>
            </IncompleteIndicator>
            <Spacer />
            <AssetCounter>
              {__('pim_asset_manager.asset_collection.asset_count', {count: value.data.length})}
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
            <Button buttonSize='medium' color='outline'>{__('pim_asset_manager.asset_collection.add_asset')}</Button>
          </SectionTitle>
          <div>
            <AssetCollection assetFamilyIdentifier={value.attribute.reference_data_name} assetCodes={value.data} context={context} />
          </div>
        </React.Fragment>
      ))}
    </React.Fragment>
  )
};

export default connect((state: AssetCollectionState): ListProps => ({
  attributes: selectAttributeList(state),
  context: selectContext(state),
  values: selectCurrentValues(state)
}))(List);


