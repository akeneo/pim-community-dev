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
import ProductLinkRuleCollection from 'akeneoassetmanager/domain/model/asset-family/product-link-rule-collection';
import {
  assetFamilyNamingConventionUpdated,
  assetFamilyProductLinkRulesUpdated,
  saveAssetFamily,
} from 'akeneoassetmanager/application/action/asset-family/edit';
import {executeProductLinkRules} from 'akeneoassetmanager/application/action/asset-family/product-link-rule';
import {canEditAssetFamily} from 'akeneoassetmanager/application/reducer/right';
import Ajv from 'ajv';
import {getErrorsViewStartedWith} from 'akeneoassetmanager/application/component/app/validation-error';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {EditionFormState} from 'akeneoassetmanager/application/reducer/asset-family/edit/form';
import {Link} from 'akeneoassetmanager/application/component/app/link';
import AssetIllustration from 'akeneoassetmanager/platform/component/visual/illustration/asset';
import {
  HelperSection,
  HelperSeparator,
  HelperTitle,
  HelperText,
} from 'akeneoassetmanager/platform/component/common/helper';

const ajv = new Ajv({allErrors: true, verbose: true});
const namingConventionSchema = require('akeneoassetmanager/infrastructure/model/asset-family-naming-convention.schema.json');
const productLinkRulesSchema = require('akeneoassetmanager/infrastructure/model/asset-family-product-link-rules.schema.json');
const securityContext = require('pim/security-context');
import {Button, ButtonContainer} from 'akeneoassetmanager/application/component/app/button';
import {ConfirmModal} from 'akeneoassetmanager/application/component/app/modal';

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
      edit_product_link_rules: boolean;
      execute_product_link_rules: boolean;
    };
  };
}

type AssetFamilyNamingConventionEditorProps = {
  namingConvention: NamingConvention;
  errors: ValidationError[];
  onAssetFamilyNamingConventionChange: (namingConvention: NamingConvention) => void;
  editMode: boolean;
};

type AssetFamilyProductLinkRulesEditorProps = {
  productLinkRules: ProductLinkRuleCollection;
  errors: ValidationError[];
  onAssetFamilyProductLinkRulesChange: (productLinkRules: ProductLinkRuleCollection) => void;
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
          onAssetFamilyNamingConventionChange(JSON.stringify(null === event ? {} : event));
        }}
        mode="code"
        schema={namingConventionSchema}
        ajv={ajv}
      />
      {getErrorsViewStartedWith(errors, 'naming_convention')}
    </div>
  );
};

const AssetFamilyProductLinkRulesEditor = ({
  productLinkRules,
  errors,
  onAssetFamilyProductLinkRulesChange,
  editMode,
}: AssetFamilyProductLinkRulesEditorProps) => {
  //https://github.com/vankop/jsoneditor-react/blob/HEAD/src/Editor.jsx
  if (!editMode) {
    return (
      <div className="AknJsonEditor">
        <Editor value={JSON.parse(productLinkRules)} mode="view" />
      </div>
    );
  }

  return (
    <div className="AknJsonEditor">
      <Editor
        value={JSON.parse(productLinkRules)}
        onChange={(event: object) => {
          onAssetFamilyProductLinkRulesChange(JSON.stringify(null === event ? [] : event));
        }}
        mode="code"
        schema={productLinkRulesSchema}
        ajv={ajv}
      />
      {getErrorsViewStartedWith(errors, 'product_link_rules')}{' '}
    </div>
  );
};

interface DispatchProps {
  events: {
    onAssetFamilyNamingConventionUpdated: (namingConvention: NamingConvention) => void;
    onAssetFamilyProductLinkRulesUpdated: (productLinkRules: ProductLinkRuleCollection) => void;
    onSaveEditForm: () => void;
    onExecuteProductLinkRules: () => void;
  };
}

class ProductLinkRule extends React.Component<StateProps & DispatchProps> {
  props: StateProps & DispatchProps;
  public state: {isExecuteRulesModalOpen: boolean} = {
    isExecuteRulesModalOpen: false,
  };

  render() {
    const {assetFamily, context, form, errors, events, rights} = this.props;
    const assetFamilyLabel = getAssetFamilyLabel(assetFamily, context.locale);

    return (
      <React.Fragment>
        <Header
          label={__('pim_asset_manager.asset_family.tab.product_link_rules')}
          image={null}
          primaryAction={(defaultFocus: React.RefObject<any>) => (
            <ButtonContainer>
              {rights.assetFamily.execute_product_link_rules ? (
                <Button color="outline" onClick={() => this.setState({isExecuteRulesModalOpen: true})} ref={defaultFocus}>
                  {__(`pim_asset_manager.asset_family.button.execute_product_link_rules`)}
                </Button>
              ) : null}
              {rights.assetFamily.edit_naming_convention ? (
                <Button color="green" onClick={events.onSaveEditForm} ref={defaultFocus}>
                  {__('pim_asset_manager.asset_family.button.save')}
                </Button>
              ) : null}
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
            👋 {__('pim_asset_manager.asset_family.product_link_rules.help.title')}
            <HelperText>
              {__('pim_asset_manager.asset_family.product_link_rules.help.description')}
              <br />
              <Link href="https://help.akeneo.com/pim/v4/articles/assets-product-link-rules.html" target="_blank">
                {__('pim_asset_manager.asset_family.product_link_rules.help.link')}
              </Link>
            </HelperText>
          </HelperTitle>
        </HelperSection>
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
        <div className="AknSubsection">
          <header className="AknSubsection-title">
            <span className="group-label">
              {__('pim_asset_manager.asset_family.product_link_rules.product_link_rules_subsection')}
            </span>
          </header>
          <div className="AknFormContainer AknFormContainer--wide">
            <AssetFamilyProductLinkRulesEditor
              productLinkRules={this.props.assetFamily.productLinkRules}
              errors={this.props.errors}
              onAssetFamilyProductLinkRulesChange={(productLinkRules: ProductLinkRuleCollection) => {
                this.props.events.onAssetFamilyProductLinkRulesUpdated(productLinkRules);
              }}
              editMode={this.props.rights.assetFamily.edit_product_link_rules}
            />
          </div>
        </div>
        {this.state.isExecuteRulesModalOpen && (
          <ConfirmModal
            titleContent={__('pim_asset_manager.asset_family.product_link_rules.execute_rules.confirm_title')}
            content={__('pim_asset_manager.asset_family.product_link_rules.execute_rules.confirm_content')}
            cancelButtonText={__('pim_asset_manager.asset_family.product_link_rules.execute_rules.cancel')}
            confirmButtonText={__('pim_asset_manager.asset_family.product_link_rules.execute_rules.execute_rules')}
            onCancel={() => {
              this.setState({isExecuteRulesModalOpen: false});
            }}
            onConfirm={() => {
              this.setState({isExecuteRulesModalOpen: false});
              events.onExecuteProductLinkRules();
            }}
          />
        )}
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
          edit_product_link_rules:
            securityContext.isGranted('akeneo_assetmanager_asset_family_edit') &&
            securityContext.isGranted('akeneo_assetmanager_asset_family_manage_product_link_rule') &&
            canEditAssetFamily(state.right.assetFamily, state.form.data.identifier),
          execute_product_link_rules:
            securityContext.isGranted('akeneo_assetmanager_asset_family_edit') &&
            securityContext.isGranted('akeneo_assetmanager_asset_family_execute_product_link_rule') &&
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
        onAssetFamilyProductLinkRulesUpdated: (productLinkRules: ProductLinkRuleCollection) => {
          dispatch(assetFamilyProductLinkRulesUpdated(productLinkRules));
        },
        onSaveEditForm: () => {
          dispatch(saveAssetFamily());
        },
        onExecuteProductLinkRules: () => {
          dispatch(executeProductLinkRules());
        },
      },
    };
  }
)(ProductLinkRule);
