import React from 'react';
import {connect} from 'react-redux';
import styled from 'styled-components';
import {Button, Dropdown, IconButton, MoreIcon, useBooleanState} from 'akeneo-design-system';
import {PimView, useSecurity, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {DeleteModal} from '@akeneo-pim-community/shared';
import {EditState as State} from 'akeneoassetmanager/application/reducer/asset/edit';
import sidebarProvider from 'akeneoassetmanager/application/configuration/sidebar';
import {AssetBreadcrumb} from 'akeneoassetmanager/application/component/app/breadcrumb';
import {saveAsset} from 'akeneoassetmanager/application/action/asset/edit';
import {deleteAsset} from 'akeneoassetmanager/application/action/asset/delete';
import EditState from 'akeneoassetmanager/application/component/app/edit-state';
import Locale from 'akeneoassetmanager/domain/model/locale';
import {channelChanged, localeChanged} from 'akeneoassetmanager/application/action/asset/user';
import LocaleSwitcher from 'akeneoassetmanager/application/component/app/locale-switcher';
import ChannelSwitcher from 'akeneoassetmanager/application/component/app/channel-switcher';
import Channel from 'akeneoassetmanager/domain/model/channel';
import {getLocales} from 'akeneoassetmanager/application/reducer/structure';
import {CompletenessBadge} from 'akeneoassetmanager/application/component/app/completeness';
import {canEditAssetFamily} from 'akeneoassetmanager/application/reducer/right';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {assetFamilyIdentifierStringValue} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {getLabel} from 'pimui/js/i18n';
import EditionAsset, {getEditionAssetCompleteness} from 'akeneoassetmanager/domain/model/asset/edition-asset';
import {MainMediaThumbnail} from 'akeneoassetmanager/application/component/asset/edit/main-media-thumbnail';
import {redirectToAssetFamilyListItem} from 'akeneoassetmanager/application/action/asset-family/router';
import {formatDateForUILocale} from 'akeneoassetmanager/tools/format-date';
import {Label} from 'akeneoassetmanager/application/component/app/label';
import {saveAndExecuteNamingConvention} from 'akeneoassetmanager/application/action/asset/save-and-execute-naming-convention';
import {ReloadPreviewProvider} from 'akeneoassetmanager/application/hooks/useReloadPreview';

interface StateProps {
  form: {
    isDirty: boolean;
  };
  context: {
    locale: string;
    channel: string;
    createdAt: string;
    updatedAt: string;
  };
  asset: EditionAsset;
  structure: {
    locales: Locale[];
    channels: Channel[];
  };
  hasEditRightOnAssetFamily: boolean;
  assetCode: AssetCode;
}

interface DispatchProps {
  events: {
    onSaveEditForm: () => void;
    onLocaleChanged: (locale: Locale) => void;
    onChannelChanged: (channel: Channel) => void;
    onDelete: (asset: EditionAsset) => void;
    backToAssetFamilyList: () => void;
    onSaveAndExecuteNamingConvention: (asset: EditionAsset) => void;
  };
}

interface EditProps extends StateProps, DispatchProps {}

const DateLabel = styled(Label)`
  margin: 0 10px;
`;

const MetaContainer = styled.div`
  display: flex;
  align-items: center;
`;

const SecondaryActionsButton = styled(IconButton)`
  margin-right: 10px;
`;

const SecondaryActions = ({
  canDelete,
  canExecuteNamingConvention,
  onSaveAndExecuteNamingConvention,
  onDelete,
}: {
  canDelete: boolean;
  canExecuteNamingConvention: boolean;
  onSaveAndExecuteNamingConvention: () => void;
  onDelete: () => void;
}) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState();

  return (
    <Dropdown>
      <SecondaryActionsButton
        icon={<MoreIcon />}
        ghost="borderless"
        level="tertiary"
        title={translate('pim_common.more_actions')}
        onClick={open}
      />
      {isOpen && (
        <Dropdown.Overlay verticalPosition="down" onClose={close}>
          <Dropdown.Header>
            <Dropdown.Title>{translate('pim_datagrid.actions.other')}</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            {canExecuteNamingConvention && (
              <Dropdown.Item onClick={onSaveAndExecuteNamingConvention}>
                {translate('pim_asset_manager.asset.button.save_and_execute_naming_convention')}
              </Dropdown.Item>
            )}
            {canDelete && (
              <Dropdown.Item onClick={onDelete}>{translate('pim_asset_manager.asset.button.delete')}</Dropdown.Item>
            )}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

const AssetEditView = ({form, asset, context, structure, events, hasEditRightOnAssetFamily}: EditProps) => {
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const [isDeleteModalOpen, openDeleteModal, closeDeleteModal] = useBooleanState();

  const onConfirmedDelete = () => {
    events.onDelete(asset);
    closeDeleteModal();
  };

  const editState = form.isDirty ? <EditState /> : '';
  const label = getLabel(asset.labels, context.locale, asset.code);
  const TabView = sidebarProvider.getView('akeneo_asset_manager_asset_edit', 'enrich');
  const completeness = getEditionAssetCompleteness(asset, context.channel, context.locale);

  const canEditAsset = isGranted('akeneo_assetmanager_asset_edit') && hasEditRightOnAssetFamily;
  const canDeleteAsset =
    isGranted('akeneo_assetmanager_asset_edit') &&
    isGranted('akeneo_assetmanager_asset_delete') &&
    hasEditRightOnAssetFamily;
  const executeNamingConventions =
    isGranted('akeneo_assetmanager_asset_edit') &&
    isGranted('akeneo_assetmanager_asset_family_execute_naming_conventions') &&
    hasEditRightOnAssetFamily;

  return (
    <ReloadPreviewProvider>
      <div className="AknDefault-contentWithColumn">
        <div className="AknDefault-thirdColumnContainer">
          <div className="AknDefault-thirdColumn" />
        </div>
        <div className="AknDefault-contentWithBottom">
          <div className="AknDefault-mainContent" data-tab="enrich">
            <header className="AknTitleContainer">
              <div className="AknTitleContainer-line">
                <MainMediaThumbnail asset={asset} context={context} />
                <div className="AknTitleContainer-mainContainer AknTitleContainer-mainContainer--contained">
                  <div>
                    <div className="AknTitleContainer-line">
                      <div className="AknTitleContainer-breadcrumbs">
                        <AssetBreadcrumb
                          assetFamilyIdentifier={assetFamilyIdentifierStringValue(asset.assetFamily.identifier)}
                          assetCode={asset.code}
                        />
                      </div>
                      <div className="AknTitleContainer-buttonsContainer">
                        <div className="AknTitleContainer-userMenuContainer user-menu">
                          <PimView
                            className={`AknTitleContainer-userMenu ${
                              canEditAsset ? '' : 'AknTitleContainer--withoutMargin'
                            }`}
                            viewName="pim-asset-family-index-user-navigation"
                          />
                        </div>
                        <div className="AknTitleContainer-actionsContainer AknButtonList">
                          <SecondaryActions
                            canExecuteNamingConvention={executeNamingConventions}
                            canDelete={canDeleteAsset}
                            onSaveAndExecuteNamingConvention={() => {
                              events.onSaveAndExecuteNamingConvention(asset);
                            }}
                            onDelete={openDeleteModal}
                          />
                          {canEditAsset ? (
                            <div className="AknTitleContainer-rightButton">
                              <Button onClick={events.onSaveEditForm}>
                                {translate('pim_asset_manager.asset.button.save')}
                              </Button>
                            </div>
                          ) : null}
                        </div>
                      </div>
                    </div>
                    <div className="AknTitleContainer-line">
                      <div className="AknTitleContainer-title">{label}</div>
                      {editState}
                    </div>
                  </div>
                  <div>
                    <div className="AknTitleContainer-line">
                      <div className="AknTitleContainer-context AknButtonList">
                        <ChannelSwitcher
                          channelCode={context.channel}
                          channels={structure.channels}
                          locale={context.locale}
                          className="AknDropdown--right"
                          onChannelChange={events.onChannelChanged}
                        />
                        <LocaleSwitcher
                          localeCode={context.locale}
                          locales={structure.locales}
                          className="AknDropdown--right"
                          onLocaleChange={events.onLocaleChanged}
                        />
                      </div>
                    </div>
                  </div>
                  <MetaContainer>
                    {completeness.hasRequiredAttribute() && (
                      <>
                        {translate('pim_common.completeness')}:&nbsp;
                        <CompletenessBadge completeness={completeness} />
                      </>
                    )}
                    <span>
                      <DateLabel>
                        {translate('pim_asset_manager.asset.created_at')}:{' '}
                        {formatDateForUILocale(context.createdAt, {
                          year: 'numeric',
                          month: 'numeric',
                          day: 'numeric',
                          hour: 'numeric',
                          minute: 'numeric',
                        })}
                      </DateLabel>
                      |
                      <DateLabel>
                        {translate('pim_asset_manager.asset.updated_at')}:{' '}
                        {formatDateForUILocale(context.updatedAt, {
                          year: 'numeric',
                          month: 'numeric',
                          day: 'numeric',
                          hour: 'numeric',
                          minute: 'numeric',
                        })}
                      </DateLabel>
                    </span>
                  </MetaContainer>
                </div>
              </div>
            </header>
            <div className="content">
              <TabView code="enrich" />
            </div>
          </div>
        </div>
      </div>
      {isDeleteModalOpen && (
        <DeleteModal
          title={translate('pim_asset_manager.asset.delete.title')}
          onConfirm={onConfirmedDelete}
          onCancel={closeDeleteModal}
        >
          {translate('pim_asset_manager.asset.delete.message', {assetLabel: label})}
        </DeleteModal>
      )}
    </ReloadPreviewProvider>
  );
};

export default connect(
  (state: State): StateProps => {
    const locale = state.user.catalogLocale;

    return {
      form: {
        isDirty: state.form.state.isDirty,
      },
      context: {
        locale,
        channel: state.user.catalogChannel,
        createdAt: state.form.data.createdAt,
        updatedAt: state.form.data.updatedAt,
      },
      asset: state.form.data,
      structure: {
        locales: getLocales(state.structure.channels, state.user.catalogChannel),
        channels: state.structure.channels,
      },
      hasEditRightOnAssetFamily: canEditAssetFamily(state.right.assetFamily, state.form.data.assetFamily.identifier),
      assetCode: state.form.data.code,
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onSaveEditForm: () => {
          dispatch(saveAsset());
        },
        onLocaleChanged: (locale: Locale) => {
          dispatch(localeChanged(locale.code));
        },
        onChannelChanged: (channel: Channel) => {
          dispatch(channelChanged(channel.code));
        },
        onDelete: (asset: EditionAsset) => {
          dispatch(deleteAsset(asset.assetFamily.identifier, asset.code));
        },
        onSaveAndExecuteNamingConvention: (asset: EditionAsset) => {
          dispatch(saveAndExecuteNamingConvention(asset.assetFamily.identifier, asset.code));
        },
        backToAssetFamilyList: () => {
          dispatch(redirectToAssetFamilyListItem());
        },
      },
    };
  }
)(AssetEditView);
