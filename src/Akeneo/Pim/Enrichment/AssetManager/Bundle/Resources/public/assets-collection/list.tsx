import * as React from 'react';
import {connect} from 'react-redux';
// import AssetSelector from "akeneoassetmanager/application/component/app/asset-selector";
// import AssetSelector from 'akeneoassetmanager/application/component/app/asset-selector'

type ListProps = {
  attributes: any[],
  values: {[key: string]: any},
  context: {locale: string, channel: string}
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
      {attributes.map((attribute: any) => (
        <div key={attribute.code}>
          {/*<AssetSelector*/}
          {/*  value={values[attribute.code].data}*/}
          {/*  assetFamilyIdentifier={}*/}
          {/*  multiple={true}*/}
          {/*  readOnly={}*/}
          {/*  compact={}*/}
          {/*  locale={}*/}
          {/*  channel={}*/}
          {/*  placeholder={}*/}
          {/*  onChange={}*/}
          {/*/>*/}
          { attribute.labels[context.locale] } = {values[attribute.code].data}
        </div>
      ))}
    </React.Fragment>
  )
};

export default connect((state: any) => ({attributes: selectAttributeList(state), context: selectContext(state)}))(List);

const selectAttributeList = (state: any) => {
  return state.structure.attributes;
};

const selectContext = (state: any) => {
  return state.context;
};
