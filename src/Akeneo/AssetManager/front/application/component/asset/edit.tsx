import React from 'react';
import {connect, useDispatch} from 'react-redux';
import styled from 'styled-components';
import {Button, Dropdown, IconButton, MoreIcon, useBooleanState} from 'akeneo-design-system';
import {
  useSecurity,
  useTranslate,
  DeleteModal,
  LocaleSelector,
  LocaleCode,
  Locale,
  getLabel,
  Channel,
  ChannelCode,
  UnsavedChanges,
  PageHeader,
  useUserContext,
} from '@akeneo-pim-community/shared';
import {EditState as State} from 'akeneoassetmanager/application/reducer/asset/edit';
import {AssetBreadcrumb} from 'akeneoassetmanager/application/component/app/breadcrumb';
import {saveAsset} from 'akeneoassetmanager/application/action/asset/edit';
import {deleteAsset} from 'akeneoassetmanager/application/action/asset/delete';
import {channelChanged, localeChanged} from 'akeneoassetmanager/application/action/asset/user';
import {ChannelSwitcher} from 'akeneoassetmanager/application/component/app/channel-switcher';
import {getLocales} from 'akeneoassetmanager/application/reducer/structure';
import {CompletenessBadge} from 'akeneoassetmanager/application/component/app/completeness';
import {canEditAssetFamily} from 'akeneoassetmanager/application/reducer/right';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {assetFamilyIdentifierStringValue} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import EditionAsset, {getEditionAssetCompleteness} from 'akeneoassetmanager/domain/model/asset/edition-asset';
import {MainMediaThumbnail} from 'akeneoassetmanager/application/component/asset/edit/main-media-thumbnail';
import {redirectToAssetFamilyListItem} from 'akeneoassetmanager/application/action/asset-family/router';
import {formatDateForUILocale} from 'akeneoassetmanager/tools/format-date';
import {Label} from 'akeneoassetmanager/application/component/app/label';
import {saveAndExecuteNamingConvention} from 'akeneoassetmanager/application/action/asset/save-and-execute-naming-convention';
import {ReloadPreviewProvider} from 'akeneoassetmanager/application/hooks/useReloadPreview';
import {UserNavigation} from 'akeneoassetmanager/application/component/app/user-navigation';
import {ContextSwitchers, ScrollablePageContent} from 'akeneoassetmanager/application/component/app/layout';
import {useTabView} from 'akeneoassetmanager/application/hooks/useTabView';
import {useAssetFetcher} from 'akeneoassetmanager/infrastructure/fetcher/useAssetFetcher';

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
    onLocaleChanged: (localeCode: LocaleCode) => void;
    onChannelChanged: (channelCode: ChannelCode) => void;
    onDelete: (asset: EditionAsset) => void;
    backToAssetFamilyList: () => void;
  };
}

interface EditProps extends StateProps, DispatchProps {}

const DateLabel = styled(Label)`
  margin: 0 10px;
`;

const MetaContainer = styled.div`
  display: flex;
  align-items: center;
  margin-top: 20px;
`;

const SecondaryActions = ({
  asset,
  canDelete,
  canExecuteNamingConvention,
  onDelete,
}: {
  asset: EditionAsset;
  canDelete: boolean;
  canExecuteNamingConvention: boolean;
  onDelete: () => void;
}) => {
  const translate = useTranslate();
  const dispatch = useDispatch();
  const assetFetcher = useAssetFetcher();
  const [isOpen, open, close] = useBooleanState();

  const handleDelete = () => {
    close();
    onDelete();
  };

  return (
    <Dropdown>
      <IconButton
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
              <Dropdown.Item
                onClick={() =>
                  dispatch(saveAndExecuteNamingConvention(assetFetcher, asset.assetFamily.identifier, asset.code))
                }
              >
                {translate('pim_asset_manager.asset.button.save_and_execute_naming_convention')}
              </Dropdown.Item>
            )}
            {canDelete && (
              <Dropdown.Item onClick={handleDelete}>{translate('pim_asset_manager.asset.button.delete')}</Dropdown.Item>
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
  const dispatch = useDispatch();
  const userContext = useUserContext();
  const assetFetcher = useAssetFetcher();
  const [isDeleteModalOpen, openDeleteModal, closeDeleteModal] = useBooleanState();

  const onConfirmedDelete = () => {
    events.onDelete(asset);
    closeDeleteModal();
  };

  const label = getLabel(asset.labels, context.locale, asset.code);
  const TabView = useTabView('akeneo_asset_manager_asset_edit', 'enrich');
  const completeness = getEditionAssetCompleteness(asset, context.channel, context.locale);
  const uiLocale = userContext.get('uiLocale');
  const timeZone = userContext.get('timezone');

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
      <ScrollablePageContent>
        <PageHeader>
          <PageHeader.Illustration>
            <MainMediaThumbnail asset={asset} context={context} />
          </PageHeader.Illustration>
          <PageHeader.Breadcrumb>
            <AssetBreadcrumb
              assetFamilyIdentifier={assetFamilyIdentifierStringValue(asset.assetFamily.identifier)}
              assetCode={asset.code}
            />
          </PageHeader.Breadcrumb>
          <PageHeader.UserActions>
            <UserNavigation />
          </PageHeader.UserActions>
          <PageHeader.Actions>
            <SecondaryActions
              asset={asset}
              canExecuteNamingConvention={executeNamingConventions}
              canDelete={canDeleteAsset}
              onDelete={openDeleteModal}
            />
            {canEditAsset && (
              <Button onClick={() => dispatch(saveAsset(assetFetcher))}>
                {translate('pim_asset_manager.asset.button.save')}
              </Button>
            )}
          </PageHeader.Actions>
          <PageHeader.State>{form.isDirty && <UnsavedChanges />}</PageHeader.State>
          <PageHeader.Title noTextTransform={label === `[${asset.code}]`}>{label}</PageHeader.Title>
          <PageHeader.Content>
            <ContextSwitchers>
              <ChannelSwitcher
                channelCode={context.channel}
                channels={structure.channels}
                locale={context.locale}
                onChannelChange={events.onChannelChanged}
              />
              <LocaleSelector value={context.locale} values={structure.locales} onChange={events.onLocaleChanged} />
            </ContextSwitchers>
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
                  {formatDateForUILocale(context.createdAt, uiLocale, timeZone, {
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
                  {formatDateForUILocale(context.updatedAt, uiLocale, timeZone, {
                    year: 'numeric',
                    month: 'numeric',
                    day: 'numeric',
                    hour: 'numeric',
                    minute: 'numeric',
                  })}
                </DateLabel>
              </span>
            </MetaContainer>
          </PageHeader.Content>
        </PageHeader>
        <TabView code="enrich" />
        {isDeleteModalOpen && (
          <DeleteModal
            title={translate('pim_asset_manager.asset.delete.title')}
            onConfirm={onConfirmedDelete}
            onCancel={closeDeleteModal}
          >
            {translate('pim_asset_manager.asset.delete.message', {assetLabel: label})}
          </DeleteModal>
        )}
      </ScrollablePageContent>
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
        onLocaleChanged: (localeCode: LocaleCode) => {
          dispatch(localeChanged(localeCode));
        },
        onChannelChanged: (channelCode: ChannelCode) => {
          dispatch(channelChanged(channelCode));
        },
        onDelete: (asset: EditionAsset) => {
          dispatch(deleteAsset(asset.assetFamily.identifier, asset.code));
        },
        backToAssetFamilyList: () => {
          dispatch(redirectToAssetFamilyListItem());
        },
      },
    };
  }
)(AssetEditView);
