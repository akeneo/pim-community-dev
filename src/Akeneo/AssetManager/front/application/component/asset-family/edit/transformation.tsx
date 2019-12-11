import * as React from 'react';
import {connect} from 'react-redux';
import __ from "akeneoassetmanager/tools/translator";
import {breadcrumbConfiguration} from "akeneoassetmanager/application/component/asset-family/edit";
import Header from "akeneoassetmanager/application/component/asset-family/edit/header";
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import TransformationCollection from "akeneoassetmanager/domain/model/asset-family/transformation/transformation-collection";
import {assetFamilyTransformationsUpdated} from 'akeneoassetmanager/application/action/asset-family/edit';

interface StateProps {
  assetFamily: AssetFamily;
}

type AssetFamilyTransformationEditorProps = {
  transformations: TransformationCollection,
  onAssetFamilyTransformationsChange: (transformations: TransformationCollection) => void,
}

const AssetFamilyTransformationEditor = ({
  transformations,
  onAssetFamilyTransformationsChange
}: AssetFamilyTransformationEditorProps) => {
  return (
    <textarea value={transformations} onChange={(event: React.ChangeEvent<HTMLTextAreaElement>) => {
      onAssetFamilyTransformationsChange(event.target.value)
    }}/>
  )
};

class Transformation extends React.Component<StateProps & DispatchProps> {
  props: StateProps & DispatchProps;

  render() {
    const assetFamily = this.props.assetFamily;

    return (
      <React.Fragment>
        <Header
          label={__('pim_asset_manager.asset_family.tab.transformations')}
          image={assetFamily.image}
          primaryAction={() => {
            return null;
          }}
          secondaryActions={() => {
            return null;
          }}
          withLocaleSwitcher={false}
          withChannelSwitcher={false}
          isDirty={false}
          breadcrumbConfiguration={breadcrumbConfiguration}
        />
        <AssetFamilyTransformationEditor
          transformations={this.props.assetFamily.transformations}
          onAssetFamilyTransformationsChange={(transformations: TransformationCollection) => {
            this.props.events.onAssetFamilyTransformationsUpdated(transformations);
          }}
        />
      </React.Fragment>
    )
  }
}

interface DispatchProps {
  events: {
    onAssetFamilyTransformationsUpdated: (transformations: TransformationCollection) => void
  };
}

export default connect(
  (state: EditState): StateProps => {
    return {
      assetFamily: state.form.data,
    }
  },
  (dispatch: any) : DispatchProps => {
    return {
      events: {
        onAssetFamilyTransformationsUpdated: (transformations: TransformationCollection) => {
          dispatch(assetFamilyTransformationsUpdated(transformations));
        },
      }
    }
  }
)(Transformation)
