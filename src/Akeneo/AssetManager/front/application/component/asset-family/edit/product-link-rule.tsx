import React from 'react';
import {connect} from 'react-redux';
import {JsonEditor as Editor} from 'jsoneditor-react';
import 'jsoneditor-react/es/editor.min.css';
import __ from 'akeneoassetmanager/tools/translator';
import {AssetFamilyBreadcrumb} from 'akeneoassetmanager/application/component/app/breadcrumb';
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
import {
  executeNamingConvention,
  executeProductLinkRules,
} from 'akeneoassetmanager/application/action/asset-family/product-link-rule';
import {canEditAssetFamily} from 'akeneoassetmanager/application/reducer/right';
import Ajv from 'ajv';
import {getErrorsViewStartedWith} from 'akeneoassetmanager/application/component/app/validation-error';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {EditionFormState} from 'akeneoassetmanager/application/reducer/asset-family/edit/form';
import {Button, ButtonContainer} from 'akeneoassetmanager/application/component/app/button';
import {ConfirmModal} from 'akeneoassetmanager/application/component/app/modal';
import namingConventionSchema from 'akeneoassetmanager/infrastructure/model/asset-family/naming-convention.schema.json';
import productLinkRulesSchema from 'akeneoassetmanager/infrastructure/model/asset-family/product-link-rules.schema.json';
import {AssetsIllustration, Information, Link} from 'akeneo-design-system';

const ajv = new Ajv({allErrors: true, verbose: true});
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
      edit_product_link_rules: boolean;
      execute_product_link_rules: boolean;
      execute_naming_conventions: boolean;
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

type SecondaryActionsProps = {
  canExecuteRules: boolean;
  onExecuteRules: () => void;
  canExecuteNamingConvention: boolean;
  onExecuteNamingConvention: () => void;
};

const SecondaryActions = ({
  canExecuteRules,
  onExecuteRules,
  canExecuteNamingConvention,
  onExecuteNamingConvention,
}: SecondaryActionsProps) => {
  if (!canExecuteRules && !canExecuteNamingConvention) {
    return null;
  }

  return (
    <div className="AknSecondaryActions AknDropdown AknButtonList-item">
      <div className="AknSecondaryActions-button dropdown-button" data-toggle="dropdown" />
      <div className="AknDropdown-menu AknDropdown-menu--right">
        <div className="AknDropdown-menuTitle">{__('pim_datagrid.actions.other')}</div>
        <div>
          {canExecuteRules && (
            <button tabIndex={-1} className="AknDropdown-menuLink" onClick={onExecuteRules}>
              {__(`pim_asset_manager.asset_family.button.execute_product_link_rules`)}
            </button>
          )}
          {canExecuteNamingConvention && (
            <button tabIndex={-1} className="AknDropdown-menuLink" onClick={onExecuteNamingConvention}>
              {__(`pim_asset_manager.asset_family.button.execute_naming_convention`)}
            </button>
          )}
        </div>
      </div>
    </div>
  );
};

interface DispatchProps {
  events: {
    onAssetFamilyNamingConventionUpdated: (namingConvention: NamingConvention) => void;
    onAssetFamilyProductLinkRulesUpdated: (productLinkRules: ProductLinkRuleCollection) => void;
    onSaveEditForm: () => void;
    onExecuteProductLinkRules: () => void;
    onExecuteNamingConvention: () => void;
  };
}

class ProductLinkRule extends React.Component<StateProps & DispatchProps> {
  props: StateProps & DispatchProps;
  public state: {
    isExecuteRulesModalOpen: boolean;
    isExecuteNamingConventionModalOpen: boolean;
  } = {
    isExecuteRulesModalOpen: false,
    isExecuteNamingConventionModalOpen: false,
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
              {rights.assetFamily.edit_naming_convention ? (
                <Button color="green" onClick={events.onSaveEditForm} ref={defaultFocus}>
                  {__('pim_asset_manager.asset_family.button.save')}
                </Button>
              ) : null}
            </ButtonContainer>
          )}
          secondaryActions={() => (
            <SecondaryActions
              canExecuteRules={rights.assetFamily.execute_product_link_rules}
              onExecuteRules={() => this.setState({isExecuteRulesModalOpen: true})}
              canExecuteNamingConvention={rights.assetFamily.execute_naming_conventions}
              onExecuteNamingConvention={() => this.setState({isExecuteNamingConventionModalOpen: true})}
            />
          )}
          withLocaleSwitcher={false}
          withChannelSwitcher={false}
          isDirty={form.state.isDirty}
          breadcrumb={<AssetFamilyBreadcrumb assetFamilyLabel={assetFamilyLabel} />}
        />
        <Information
          illustration={<AssetsIllustration />}
          title={`👋 ${__('pim_asset_manager.asset_family.product_link_rules.help.title')}`}
        >
          <p>{__('pim_asset_manager.asset_family.product_link_rules.help.description')}</p>
          <Link href="https://help.akeneo.com/pim/v4/articles/assets-product-link-rules.html" target="_blank">
            {__('pim_asset_manager.asset_family.product_link_rules.help.link')}
          </Link>
        </Information>
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
        {this.state.isExecuteNamingConventionModalOpen && (
          <ConfirmModal
            titleContent={__(
              'pim_asset_manager.asset_family.product_link_rules.execute_naming_convention.confirm_title'
            )}
            content={__('pim_asset_manager.asset_family.product_link_rules.execute_naming_convention.confirm_content')}
            cancelButtonText={__('pim_asset_manager.asset_family.product_link_rules.execute_naming_convention.cancel')}
            confirmButtonText={__(
              'pim_asset_manager.asset_family.product_link_rules.execute_naming_convention.execute_naming_convention'
            )}
            onCancel={() => {
              this.setState({isExecuteNamingConventionModalOpen: false});
            }}
            onConfirm={() => {
              this.setState({isExecuteNamingConventionModalOpen: false});
              events.onExecuteNamingConvention();
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
          execute_naming_conventions:
            securityContext.isGranted('akeneo_assetmanager_asset_family_edit') &&
            securityContext.isGranted('akeneo_assetmanager_asset_family_execute_naming_conventions') &&
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
        onExecuteNamingConvention: () => {
          dispatch(executeNamingConvention());
        },
      },
    };
  }
)(ProductLinkRule);
