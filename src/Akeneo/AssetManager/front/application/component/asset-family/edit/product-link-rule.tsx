import * as React from 'react';
import {connect} from 'react-redux';
import {JsonEditor as Editor} from 'jsoneditor-react';
import 'jsoneditor-react/es/editor.min.css';
import __ from 'akeneoassetmanager/tools/translator';
import {breadcrumbConfiguration} from 'akeneoassetmanager/application/component/asset-family/edit';
import Header from 'akeneoassetmanager/application/component/asset-family/edit/header';
import {AssetFamily, getAssetFamilyLabel} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import NamingConvention from 'akeneoassetmanager/domain/model/asset-family/naming-convention';
import {
  assetFamilyNamingConventionUpdated,
  saveAssetFamily,
} from 'akeneoassetmanager/application/action/asset-family/edit';
import {canEditAssetFamily} from 'akeneoassetmanager/application/reducer/right';
import Ajv from 'ajv';
import {getErrorsViewStartedWith} from 'akeneoassetmanager/application/component/app/validation-error';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {EditionFormState} from 'akeneoassetmanager/application/reducer/asset-family/edit/form';
const ajv = new Ajv({allErrors: true, verbose: true});
const schema = require('akeneoassetmanager/infrastructure/model/asset-family-naming-convention.schema.json');
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
      edit_naming_convention: boolean;
    };
  };
}

type AssetFamilyNamingConventionEditorProps = {
  namingConvention: NamingConvention;
  errors: ValidationError[];
  onAssetFamilyNamingConventionChange: (namingConvention: NamingConvention) => void;
  editMode: boolean;
};

const AssetFamilyNamingConventionEditor = ({
  namingConvention,
  errors,
  onAssetFamilyNamingConventionChange,
  editMode,
}: AssetFamilyNamingConventionEditorProps) => {
  //https://github.com/vankop/jsoneditor-react/blob/HEAD/src/Editor.jsx
  if (!editMode) {
    return (
      <div className="AknJsonEditor">
        <Editor value={JSON.parse(namingConvention)} mode="view" />
      </div>
    );
  }

  return (
    <div className="AknJsonEditor">
      <Editor
        value={JSON.parse(namingConvention)}
        onChange={(event: object) => {
          onAssetFamilyNamingConventionChange(JSON.stringify(event));
        }}
        mode="code"
        schema={schema}
        ajv={ajv}
      />
      {getErrorsViewStartedWith(errors, 'naming_convention')}
    </div>
  );
};

interface DispatchProps {
  events: {
    onAssetFamilyNamingConventionUpdated: (namingConvention: NamingConvention) => void;
    onSaveEditForm: () => void;
  };
}

class ProductLinkRule extends React.Component<StateProps & DispatchProps, ProductLinkRule> {
  props: StateProps & DispatchProps;

  render() {
    const {assetFamily, context, form, errors, events, rights} = this.props;
    const assetFamilyLabel = getAssetFamilyLabel(assetFamily, context.locale);

    return (
      <React.Fragment>
        <Header
          label={__('pim_asset_manager.asset_family.tab.product_link_rules')}
          image={null}
          primaryAction={(defaultFocus: React.RefObject<any>) =>
            rights.assetFamily.edit_naming_convention ? (
              <button className="AknButton AknButton--apply" onClick={events.onSaveEditForm} ref={defaultFocus}>
                {__('pim_asset_manager.asset_family.button.save')}
              </button>
            ) : null
          }
          secondaryActions={() => null}
          withLocaleSwitcher={false}
          withChannelSwitcher={false}
          isDirty={form.state.isDirty}
          breadcrumbConfiguration={breadcrumbConfiguration(assetFamily.identifier, assetFamilyLabel)}
        />
        <div className="AknSubsection">
          <header className="AknSubsection-title">
            <span className="group-label">
              {__('pim_asset_manager.asset_family.product_link_rules.naming_convention_subsection')}
            </span>
          </header>
          <div className="AknFormContainer AknFormContainer--wide">
            <AssetFamilyNamingConventionEditor
              namingConvention={assetFamily.namingConvention}
              errors={errors}
              onAssetFamilyNamingConventionChange={events.onAssetFamilyNamingConventionUpdated}
              editMode={rights.assetFamily.edit_naming_convention}
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
          edit_naming_convention:
            securityContext.isGranted('akeneo_assetmanager_asset_family_edit') &&
            securityContext.isGranted('akeneo_assetmanager_asset_family_manage_product_link_rule') &&
            canEditAssetFamily(state.right.assetFamily, state.form.data.identifier),
        },
      },
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onAssetFamilyNamingConventionUpdated: (namingConvention: NamingConvention) => {
          dispatch(assetFamilyNamingConventionUpdated(namingConvention));
        },
        onSaveEditForm: () => {
          dispatch(saveAssetFamily());
        },
      },
    };
  }
)(ProductLinkRule);
