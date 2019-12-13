import * as React from 'react';
import {connect} from 'react-redux';
import {JsonEditor as Editor} from 'jsoneditor-react';
import 'jsoneditor-react/es/editor.min.css';
import __ from 'akeneoassetmanager/tools/translator';
import {breadcrumbConfiguration} from 'akeneoassetmanager/application/component/asset-family/edit';
import Header from 'akeneoassetmanager/application/component/asset-family/edit/header';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import TransformationCollection from 'akeneoassetmanager/domain/model/asset-family/transformation/transformation-collection';
import {
  assetFamilyTransformationsUpdated,
  saveAssetFamily,
} from 'akeneoassetmanager/application/action/asset-family/edit';
import Ajv from 'ajv';
import {getErrorsView} from "akeneoassetmanager/application/component/app/validation-error";
import ValidationError from "akeneoassetmanager/domain/model/validation-error";
const ajv = new Ajv({allErrors: true, verbose: true});
const schema = require('akeneoassetmanager/infrastructure/model/asset-family-transformations.schema.json');

interface StateProps {
  assetFamily: AssetFamily;
  errors: ValidationError[];
}

type AssetFamilyTransformationEditorProps = {
  transformations: TransformationCollection;
  errors: ValidationError[];
  onAssetFamilyTransformationsChange: (transformations: TransformationCollection) => void;
};

const AssetFamilyTransformationEditor = ({
  transformations,
  errors,
  onAssetFamilyTransformationsChange,
}: AssetFamilyTransformationEditorProps) => {
  //https://github.com/vankop/jsoneditor-react/blob/HEAD/src/Editor.jsx
  return (
    <div className="AknJsonEditor">
      <Editor
        value={JSON.parse(transformations)}
        onChange={(event: object) => {
          onAssetFamilyTransformationsChange(JSON.stringify(event));
        }}
        mode="code"
        schema={schema}
        ajv={ajv}
      />
      {getErrorsView(errors, 'transformations', (field: string) => (error: ValidationError) =>
        error.propertyPath.indexOf(field) === 0
      )}
    </div>
  );
};

interface DispatchProps {
  events: {
    onAssetFamilyTransformationsUpdated: (transformations: TransformationCollection) => void;
    onSaveEditForm: () => void;
  };
}

class Transformation extends React.Component<StateProps & DispatchProps, Transformation> {
  props: StateProps & DispatchProps;

  render() {
    return (
      <React.Fragment>
        <Header
          label={__('pim_asset_manager.asset_family.tab.transformations')}
          image={null}
          primaryAction={(defaultFocus: React.RefObject<any>) => (
            <button
              className="AknButton AknButton--apply"
              onClick={this.props.events.onSaveEditForm}
              ref={defaultFocus}
            >
              {__('pim_asset_manager.asset_family.button.save')}
            </button>
          )}
          secondaryActions={() => {
            return null;
          }}
          withLocaleSwitcher={false}
          withChannelSwitcher={false}
          isDirty={false}
          breadcrumbConfiguration={breadcrumbConfiguration}
        />
        <div className="AknDescriptionHeader AknDescriptionHeader--sticky">
          <div
            className="AknDescriptionHeader-icon"
            style={{backgroundImage: 'url("/bundles/pimui/images/illustrations/Asset.svg")'}}
          />
          <div className="AknDescriptionHeader-title">
            {__('pim_asset_manager.asset_family.transformations.help.title')}
            <div className="AknDescriptionHeader-description">
              {__('pim_asset_manager.asset_family.transformations.help.description')}
              <br />
              <a href="https://help.akeneo.com/" className="AknDescriptionHeader-link">
                {__('pim_asset_manager.asset_family.transformations.help.link')}
              </a>
              <br />
            </div>
          </div>
        </div>
        <div className="AknSubsection">
          <header className="AknSubsection-title">
            <span className="group-label">{__('pim_asset_manager.asset_family.transformations.subsection')}</span>
          </header>
          <div className="AknFormContainer AknFormContainer--wide">
            <AssetFamilyTransformationEditor
              transformations={this.props.assetFamily.transformations}
              errors={this.props.errors}
              onAssetFamilyTransformationsChange={(transformations: TransformationCollection) => {
                this.props.events.onAssetFamilyTransformationsUpdated(transformations);
              }}
            />
          </div>
        </div>
      </React.Fragment>
    );
  }
}

export default connect(
  (state: EditState): StateProps => {
    return {
      assetFamily: state.form.data,
      errors: state.form.errors
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onAssetFamilyTransformationsUpdated: (transformations: TransformationCollection) => {
          dispatch(assetFamilyTransformationsUpdated(transformations));
        },
        onSaveEditForm: () => {
          dispatch(saveAssetFamily());
        },
      },
    };
  }
)(Transformation);
