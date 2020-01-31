import * as React from 'react';
import {connect} from 'react-redux';
import {JsonEditor as Editor} from 'jsoneditor-react';
import 'jsoneditor-react/es/editor.min.css';
import __ from 'akeneoassetmanager/tools/translator';
import {breadcrumbConfiguration} from 'akeneoassetmanager/application/component/asset-family/edit';
import Header from 'akeneoassetmanager/application/component/asset-family/edit/header';
import {AssetFamily, getAssetFamilyLabel} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
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
import {Link} from 'akeneoassetmanager/application/component/app/link';
import {Button, ButtonContainer} from 'akeneoassetmanager/application/component/app/button';
import AssetIllustration from 'akeneoassetmanager/platform/component/visual/illustration/asset';
import {
  HelperSection,
  HelperSeparator,
  HelperTitle,
  HelperText,
} from 'akeneoassetmanager/platform/component/common/helper';

const ajv = new Ajv({allErrors: true, verbose: true});
const schema = require('akeneoassetmanager/infrastructure/model/asset-family-transformations.schema.json');
const securityContext = require('pim/security-context');

interface StateProps {
  form: EditionFormState;
  assetFamily: AssetFamily;
  context: {
    locale: string;
  };
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

class Transformation extends React.Component<StateProps & DispatchProps, Transformation> {
  props: StateProps & DispatchProps;

  render() {
    const {assetFamily, context, events, rights, form, errors} = this.props;
    const assetFamilyLabel = getAssetFamilyLabel(assetFamily, context.locale);

    return (
      <React.Fragment>
        <Header
          label={__('pim_asset_manager.asset_family.tab.transformations')}
          image={null}
          primaryAction={(defaultFocus: React.RefObject<any>) => (
            <ButtonContainer>
              <Button color="outline" onClick={events.onLaunchComputeTransformations}>
                {__('pim_asset_manager.asset.button.launch_transformations')}
              </Button>
              {rights.assetFamily.edit && (
                <Button color="green" onClick={events.onSaveEditForm} ref={defaultFocus}>
                  {__('pim_asset_manager.asset_family.button.save')}
                </Button>
              )}
            </ButtonContainer>
          )}
          secondaryActions={() => null}
          withLocaleSwitcher={false}
          withChannelSwitcher={false}
          isDirty={form.state.isDirty}
          breadcrumbConfiguration={breadcrumbConfiguration(assetFamily.identifier, assetFamilyLabel)}
        />
        <HelperSection>
          <AssetIllustration size={80} />
          <HelperSeparator />
          <HelperTitle>
            👋 {__('pim_asset_manager.asset_family.transformations.help.title')}
            <HelperText>
              {__('pim_asset_manager.asset_family.transformations.help.description')}
              <br />
              <Link href="https://help.akeneo.com/pim/v4/articles/assets-transformation.html" target="_blank">
                {__('pim_asset_manager.asset_family.transformations.help.link')}
              </Link>
            </HelperText>
          </HelperTitle>
        </HelperSection>
        <div className="AknSubsection">
          <header className="AknSubsection-title">
            <span className="group-label">{__('pim_asset_manager.asset_family.transformations.subsection')}</span>
          </header>
          <div className="AknFormContainer AknFormContainer--wide">
            <AssetFamilyTransformationEditor
              transformations={assetFamily.transformations}
              errors={errors}
              onAssetFamilyTransformationsChange={events.onAssetFamilyTransformationsUpdated}
              editMode={rights.assetFamily.edit}
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
      context: {
        locale: state.user.catalogLocale,
      },
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
