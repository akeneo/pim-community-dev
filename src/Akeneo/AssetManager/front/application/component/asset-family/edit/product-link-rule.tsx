import React from 'react';
import {connect} from 'react-redux';
import {JsonEditor as Editor} from 'jsoneditor-react';
import 'jsoneditor-react/es/editor.min.css';
import {
  Link,
  Button,
  Helper,
  SectionTitle,
  useBooleanState,
  IconButton,
  Dropdown,
  MoreIcon,
} from 'akeneo-design-system';
import {useTranslate, Section, ValidationError, useSecurity} from '@akeneo-pim-community/shared';
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
import {EditionFormState} from 'akeneoassetmanager/application/reducer/asset-family/edit/form';
import {ConfirmModal} from 'akeneoassetmanager/application/component/app/modal';
import namingConventionSchema from 'akeneoassetmanager/infrastructure/model/asset-family/naming-convention.schema.json';
import productLinkRulesSchema from 'akeneoassetmanager/infrastructure/model/asset-family/product-link-rules.schema.json';

const ajv = new Ajv({allErrors: true, verbose: true});

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
  const translate = useTranslate();
  const [isDropdownOpen, openDropdown, closeDropdown] = useBooleanState();

  const handleItemClick = (callback: () => void) => () => {
    closeDropdown();
    callback();
  };

  if (!canExecuteRules && !canExecuteNamingConvention) {
    return null;
  }

  return (
    <Dropdown>
      <IconButton
        title={translate('pim_common.other_actions')}
        icon={<MoreIcon />}
        level="tertiary"
        ghost="borderless"
        onClick={openDropdown}
      />
      {isDropdownOpen && (
        <Dropdown.Overlay onClose={closeDropdown}>
          <Dropdown.Header>
            <Dropdown.Title>{translate('pim_common.other_actions')}</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            {canExecuteRules && (
              <Dropdown.Item onClick={handleItemClick(onExecuteRules)}>
                {translate('pim_asset_manager.asset_family.button.execute_product_link_rules')}
              </Dropdown.Item>
            )}
            {canExecuteNamingConvention && (
              <Dropdown.Item onClick={handleItemClick(onExecuteNamingConvention)}>
                {translate('pim_asset_manager.asset_family.button.execute_naming_convention')}
              </Dropdown.Item>
            )}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
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

const ProductLinkRule = ({assetFamily, context, form, errors, events, rights}: StateProps & DispatchProps) => {
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const [isExecuteRulesModalOpen, openExecuteRulesModal, closeExecuteRulesModal] = useBooleanState();
  const [
    isExecuteNamingConventionModalOpen,
    openExecuteNamingConventionModal,
    closeExecuteNamingConventionModal,
  ] = useBooleanState();
  const assetFamilyLabel = getAssetFamilyLabel(assetFamily, context.locale);

  const canEditNamingConvention =
    isGranted('akeneo_assetmanager_asset_family_edit') &&
    isGranted('akeneo_assetmanager_asset_family_manage_product_link_rule') &&
    rights.assetFamily.edit;
  const canEditProductLinkRules =
    isGranted('akeneo_assetmanager_asset_family_edit') &&
    isGranted('akeneo_assetmanager_asset_family_manage_product_link_rule') &&
    rights.assetFamily.edit;
  const canExecuteProductLinkRules =
    isGranted('akeneo_assetmanager_asset_family_edit') &&
    isGranted('akeneo_assetmanager_asset_family_execute_product_link_rule') &&
    rights.assetFamily.edit;
  const canExecuteNamingConvention =
    isGranted('akeneo_assetmanager_asset_family_edit') &&
    isGranted('akeneo_assetmanager_asset_family_execute_naming_conventions') &&
    rights.assetFamily.edit;

  return (
    <>
      <Header
        label={translate('pim_asset_manager.asset_family.tab.product_link_rules')}
        image={null}
        primaryAction={(defaultFocus: React.RefObject<any>) =>
          canEditNamingConvention ? (
            <Button onClick={events.onSaveEditForm} ref={defaultFocus}>
              {translate('pim_asset_manager.asset_family.button.save')}
            </Button>
          ) : null
        }
        secondaryActions={
          <SecondaryActions
            canExecuteRules={canExecuteProductLinkRules}
            onExecuteRules={openExecuteRulesModal}
            canExecuteNamingConvention={canExecuteNamingConvention}
            onExecuteNamingConvention={openExecuteNamingConventionModal}
          />
        }
        withLocaleSwitcher={false}
        withChannelSwitcher={false}
        isDirty={form.state.isDirty}
        breadcrumb={<AssetFamilyBreadcrumb assetFamilyLabel={assetFamilyLabel} />}
      />
      <Section>
        <div>
          <SectionTitle>
            <SectionTitle.Title>
              {translate('pim_asset_manager.asset_family.product_link_rules.naming_convention_subsection')}
            </SectionTitle.Title>
          </SectionTitle>
          <Helper>
            {translate('pim_asset_manager.asset_family.naming_convention.help.description')}&nbsp;
            <Link
              href="https://help.akeneo.com/pim/serenity/articles/assets-product-link-rules.html#focus-on-the-naming-convention"
              target="_blank"
            >
              {translate('pim_asset_manager.asset_family.naming_convention.help.link')}
            </Link>
          </Helper>
        </div>
        <AssetFamilyNamingConventionEditor
          namingConvention={assetFamily.namingConvention}
          errors={errors}
          onAssetFamilyNamingConventionChange={events.onAssetFamilyNamingConventionUpdated}
          editMode={canEditNamingConvention}
        />
      </Section>
      <Section>
        <div>
          <SectionTitle>
            <SectionTitle.Title>
              {translate('pim_asset_manager.asset_family.product_link_rules.product_link_rules_subsection')}
            </SectionTitle.Title>
          </SectionTitle>
          <Helper>
            {translate('pim_asset_manager.asset_family.product_link_rules.help.description')}&nbsp;
            <Link href="https://help.akeneo.com/pim/serenity/articles/assets-product-link-rules.html" target="_blank">
              {translate('pim_asset_manager.asset_family.product_link_rules.help.link')}
            </Link>
          </Helper>
        </div>
        <AssetFamilyProductLinkRulesEditor
          productLinkRules={assetFamily.productLinkRules}
          errors={errors}
          onAssetFamilyProductLinkRulesChange={(productLinkRules: ProductLinkRuleCollection) => {
            events.onAssetFamilyProductLinkRulesUpdated(productLinkRules);
          }}
          editMode={canEditProductLinkRules}
        />
      </Section>
      {isExecuteRulesModalOpen && (
        <ConfirmModal
          titleContent={translate('pim_asset_manager.asset_family.product_link_rules.execute_rules.confirm_title')}
          content={translate('pim_asset_manager.asset_family.product_link_rules.execute_rules.confirm_content')}
          confirmButtonText={translate('pim_asset_manager.asset_family.product_link_rules.execute_rules.execute_rules')}
          onCancel={closeExecuteRulesModal}
          onConfirm={() => {
            closeExecuteRulesModal();
            events.onExecuteProductLinkRules();
          }}
        />
      )}
      {isExecuteNamingConventionModalOpen && (
        <ConfirmModal
          titleContent={translate(
            'pim_asset_manager.asset_family.product_link_rules.execute_naming_convention.confirm_title'
          )}
          content={translate(
            'pim_asset_manager.asset_family.product_link_rules.execute_naming_convention.confirm_content'
          )}
          confirmButtonText={translate(
            'pim_asset_manager.asset_family.product_link_rules.execute_naming_convention.execute_naming_convention'
          )}
          onCancel={closeExecuteNamingConventionModal}
          onConfirm={() => {
            closeExecuteNamingConventionModal();
            events.onExecuteNamingConvention();
          }}
        />
      )}
    </>
  );
};

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
          edit: canEditAssetFamily(state.right.assetFamily, state.form.data.identifier),
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
