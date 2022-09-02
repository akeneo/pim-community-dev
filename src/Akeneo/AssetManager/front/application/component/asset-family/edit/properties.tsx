import React from 'react';
import {connect, useDispatch} from 'react-redux';
import {Button, Dropdown, IconButton, MoreIcon, SectionTitle, useBooleanState} from 'akeneo-design-system';
import {
  useTranslate,
  DeleteModal,
  useSecurity,
  PageHeader,
  UnsavedChanges,
  Locale,
  LocaleSelector,
  LocaleCode,
  PageContent,
  Section,
} from '@akeneo-pim-community/shared';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {EditForm} from 'akeneoassetmanager/application/component/asset-family/edit/form';
import {
  assetFamilyLabelUpdated,
  saveAssetFamily,
  attributeAsMainMediaUpdated,
} from 'akeneoassetmanager/application/action/asset-family/edit';
import {deleteAssetFamily} from 'akeneoassetmanager/application/action/asset-family/delete';
import {EditionFormState} from 'akeneoassetmanager/application/reducer/asset-family/edit/form';
import {AssetFamily, getAssetFamilyLabel} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {AssetFamilyBreadcrumb} from 'akeneoassetmanager/application/component/app/breadcrumb';
import {canEditAssetFamily, canEditLocale} from 'akeneoassetmanager/application/reducer/right';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {catalogLocaleChanged} from 'akeneoassetmanager/domain/event/user';
import {UserNavigation} from 'akeneoassetmanager/application/component/app/user-navigation';
import {ContextSwitchers} from 'akeneoassetmanager/application/component/app/layout';
import {useAssetFamilyFetcher} from 'akeneoassetmanager/infrastructure/fetcher/useAssetFamilyFetcher';

interface StateProps {
  form: EditionFormState;
  attributes: NormalizedAttribute[] | null;
  context: {
    locale: string;
  };
  structure: {
    locales: Locale[];
  };
  rights: {
    locale: {
      edit: boolean;
    };
    assetFamily: {
      edit: boolean;
    };
  };
}

interface DispatchProps {
  events: {
    form: {
      onLabelUpdated: (value: string, locale: string) => void;
      onAttributeAsMainMediaUpdated: (attributeAsMainMedia: AttributeIdentifier) => void;
    };
    onDelete: (assetFamily: AssetFamily) => void;
    onLocaleChanged: (localeCode: LocaleCode) => void;
  };
}

type SecondaryActionsProps = {
  canDeleteAssetFamily: boolean;
  onDeleteAssetFamily: () => void;
};

const SecondaryActions = ({canDeleteAssetFamily, onDeleteAssetFamily}: SecondaryActionsProps) => {
  const translate = useTranslate();
  const [isDropdownOpen, openDropdown, closeDropdown] = useBooleanState();

  const handleItemClick = (callback: () => void) => () => {
    closeDropdown();
    callback();
  };

  if (!canDeleteAssetFamily) {
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
            <Dropdown.Item onClick={handleItemClick(onDeleteAssetFamily)}>
              {translate('pim_asset_manager.asset_family.module.delete.button')}
            </Dropdown.Item>
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

const Properties = ({events, attributes, context, form, rights, structure}: StateProps & DispatchProps) => {
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const dispatch = useDispatch();
  const assetFamilyFetcher = useAssetFamilyFetcher();

  const [isDeleteModalOpen, openDeleteModal, closeDeleteModal] = useBooleanState();
  const assetFamily = form.data;
  const assetFamilyLabel = getAssetFamilyLabel(assetFamily, context.locale);
  const canEditAssetFamily = isGranted('akeneo_assetmanager_asset_family_edit') && rights.assetFamily.edit;
  const canDeleteAssetFamily = canEditAssetFamily && isGranted('akeneo_assetmanager_asset_family_delete');

  const handleDeleteAssetFamily = () => {
    events.onDelete(assetFamily);
    closeDeleteModal();
  };

  return (
    <>
      <PageHeader>
        <PageHeader.Breadcrumb>
          <AssetFamilyBreadcrumb assetFamilyLabel={assetFamilyLabel} />
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <UserNavigation />
        </PageHeader.UserActions>
        <PageHeader.Actions>
          <SecondaryActions canDeleteAssetFamily={canDeleteAssetFamily} onDeleteAssetFamily={openDeleteModal} />
          {canEditAssetFamily && (
            <Button onClick={() => dispatch(saveAssetFamily(assetFamilyFetcher))}>
              {translate('pim_asset_manager.asset_family.button.save')}
            </Button>
          )}
        </PageHeader.Actions>
        <PageHeader.State>{form.state.isDirty && <UnsavedChanges />}</PageHeader.State>
        <PageHeader.Title>{translate('pim_asset_manager.asset_family.tab.properties')}</PageHeader.Title>
        <PageHeader.Content>
          {0 < structure.locales.length && (
            <ContextSwitchers>
              <LocaleSelector value={context.locale} values={structure.locales} onChange={events.onLocaleChanged} />
            </ContextSwitchers>
          )}
        </PageHeader.Content>
      </PageHeader>
      <PageContent>
        <Section>
          <SectionTitle>
            <SectionTitle.Title>{translate('pim_asset_manager.asset_family.properties.title')}</SectionTitle.Title>
          </SectionTitle>
          <EditForm
            attributes={attributes}
            onLabelUpdated={events.form.onLabelUpdated}
            onAttributeAsMainMediaUpdated={events.form.onAttributeAsMainMediaUpdated}
            onSubmit={() => dispatch(saveAssetFamily(assetFamilyFetcher))}
            locale={context.locale}
            data={form.data}
            errors={form.errors}
            rights={rights}
          />
        </Section>
      </PageContent>
      {isDeleteModalOpen && (
        <DeleteModal
          title={translate('pim_asset_manager.asset_family.delete.title')}
          onConfirm={handleDeleteAssetFamily}
          onCancel={closeDeleteModal}
        >
          {translate('pim_asset_manager.asset_family.delete.message', {assetFamilyLabel})}
        </DeleteModal>
      )}
    </>
  );
};

export default connect(
  (state: EditState): StateProps => {
    const locale = state.user.catalogLocale;

    return {
      attributes: state.attributes.attributes,
      form: state.form,
      context: {
        locale: locale,
      },
      structure: {
        locales: state.structure.locales,
      },
      rights: {
        locale: {
          edit: canEditLocale(state.right.locale, locale),
        },
        assetFamily: {
          edit: canEditAssetFamily(state.right.assetFamily, state.form.data.identifier),
        },
      },
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        form: {
          onLabelUpdated: (value: string, locale: string) => {
            dispatch(assetFamilyLabelUpdated(value, locale));
          },
          onAttributeAsMainMediaUpdated: (attributeAsMainMedia: AttributeIdentifier) => {
            dispatch(attributeAsMainMediaUpdated(attributeAsMainMedia));
          },
        },
        onDelete: (assetFamily: AssetFamily) => {
          dispatch(deleteAssetFamily(assetFamily));
        },
        onLocaleChanged: (localeCode: LocaleCode) => {
          dispatch(catalogLocaleChanged(localeCode));
        },
      },
    };
  }
)(Properties);
