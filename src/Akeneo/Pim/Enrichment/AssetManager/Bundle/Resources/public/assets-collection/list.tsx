import * as React from 'react';
import {connect} from 'react-redux';
import {AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';
import {selectAttributeList, Attribute} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/structure';
import {selectContext, ContextState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
import {ValueCollection, Value, selectCurrentValues} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';
import AssetSelector from 'akeneoassetmanager/application/component/app/asset-selector';

type ListProps = {
  attributes: Attribute[],
  values: ValueCollection,
  context: ContextState
}

// value: AssetCode[] | AssetCode | null;
// assetFamilyIdentifier: AssetFamilyIdentifier;
// multiple?: boolean;
// readOnly?: boolean;
// compact?: boolean;
// locale: LocaleReference;
// channel: ChannelReference;
// placeholder: string;
// onChange: (value: AssetCode[] | AssetCode | null) => void;

const List = ({attributes, values, context}: ListProps) => {
  return (
    <React.Fragment>
      {values.map((value: Value) => (
        <div key={value.attribute.code}>
          <AssetSelector
            value={value.data}
            assetFamilyIdentifier={value.attribute.reference_data_name}
            multiple={true}
            readOnly={!value.editable}
            compact={false}
            locale={context.locale}
            channel={context.channel}
            placeholder={'nice'}
            onChange={() => {}}
          />
        </div>
      ))}
    </React.Fragment>
  )
};

export default connect((state: AssetCollectionState): ListProps => ({
  attributes: selectAttributeList(state),
  context: selectContext(state),
  values: selectCurrentValues(state)
}))(List);


