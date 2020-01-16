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
import {launchComputeTransformations} from 'akeneoassetmanager/application/action/asset-family/transformation';
import {canEditAssetFamily} from 'akeneoassetmanager/application/reducer/right';
import Ajv from 'ajv';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {EditionFormState} from 'akeneoassetmanager/application/reducer/asset-family/edit/form';
const ajv = new Ajv({allErrors: true, verbose: true});
const schema = require('akeneoassetmanager/infrastructure/model/asset-family-transformations.schema.json');
const securityContext = require('pim/security-context');

interface StateProps {
  form: EditionFormState;
  assetFamily: AssetFamily;
  errors: ValidationError[];
  rights: {
    assetFamily: {
      edit: boolean;
    };
  };
}

type AssetFamilyTransformationEditorProps = {
  transformations: TransformationCollection;
  errors: ValidationError[];
  onAssetFamilyTransformationsChange: (transformations: TransformationCollection) => void;
  editMode: boolean;
};

const AssetFamilyTransformationEditor = ({
  transformations,
  errors,
  onAssetFamilyTransformationsChange,
  editMode,
}: AssetFamilyTransformationEditorProps) => {
  //https://github.com/vankop/jsoneditor-react/blob/HEAD/src/Editor.jsx
  if (!editMode) {
    return (
      <div className="AknJsonEditor">
        <Editor value={JSON.parse(transformations)} mode="view" />
      </div>
    );
  }

  return (
    <div className="AknJsonEditor">
      <Editor
        value={JSON.parse(transformations)}
        onChange={(event: object) => {
          onAssetFamilyTransformationsChange(JSON.stringify(null === event ? [] : event));
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
    onLaunchComputeTransformations: () => void;
  };
}

const SecondaryActions = ({onLaunchComputeTransformations}: {onLaunchComputeTransformations: () => void}) => {
  return (
    <>
      <div className="AknSecondaryActions AknDropdown AknButtonList-item">
        <div className="AknSecondaryActions-button dropdown-button" data-toggle="dropdown" />
        <div className="AknDropdown-menu AknDropdown-menu--right">
          <div className="AknDropdown-menuTitle">{__('pim_datagrid.actions.other')}</div>
          <div>
            <button tabIndex={-1} className="AknDropdown-menuLink" onClick={() => onLaunchComputeTransformations()}>
              {__('pim_asset_manager.asset.button.launch_transformations')}
            </button>
          </div>
        </div>
      </div>
    </>
  );
};

class Transformation extends React.Component<StateProps & DispatchProps, Transformation> {
  props: StateProps & DispatchProps;

  render() {
    return (
      <React.Fragment>
        <Header
          label={__('pim_asset_manager.asset_family.tab.transformations')}
          image={null}
          primaryAction={(defaultFocus: React.RefObject<any>) => {
            return this.props.rights.assetFamily.edit ? (
              <button
                className="AknButton AknButton--apply"
                onClick={this.props.events.onSaveEditForm}
                ref={defaultFocus}
              >
                {__('pim_asset_manager.asset_family.button.save')}
              </button>
            ) : null;
          }}
          secondaryActions={() => {
            return (
              <SecondaryActions
                onLaunchComputeTransformations={() => {
                  this.props.events.onLaunchComputeTransformations();
                }}
              />
            );
          }}
          withLocaleSwitcher={false}
          withChannelSwitcher={false}
          isDirty={this.props.form.state.isDirty}
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
              editMode={this.props.rights.assetFamily.edit}
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
      form: state.form,
      assetFamily: state.form.data,
      errors: state.form.errors,
      rights: {
        assetFamily: {
          edit:
            securityContext.isGranted('akeneo_assetmanager_asset_family_edit') &&
            securityContext.isGranted('akeneo_assetmanager_asset_family_manage_transformation') &&
            canEditAssetFamily(state.right.assetFamily, state.form.data.identifier),
        },
      },
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
        onLaunchComputeTransformations: () => {
          dispatch(launchComputeTransformations());
        },
      },
    };
  }
)(Transformation);
