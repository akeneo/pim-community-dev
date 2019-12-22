import * as React from 'react';
import {connect} from 'react-redux';
import {EditState as State} from 'akeneoassetmanager/application/reducer/asset/edit';
import Sidebar from 'akeneoassetmanager/application/component/app/sidebar';
import {Tab} from 'akeneoassetmanager/application/reducer/sidebar';
import sidebarProvider from 'akeneoassetmanager/application/configuration/sidebar';
import Breadcrumb from 'akeneoassetmanager/application/component/app/breadcrumb';
import __ from 'akeneoassetmanager/tools/translator';
import PimView from 'akeneoassetmanager/infrastructure/component/pim-view';
import {backToAssetFamily, saveAsset} from 'akeneoassetmanager/application/action/asset/edit';
import {deleteAsset} from 'akeneoassetmanager/application/action/asset/delete';
import EditState from 'akeneoassetmanager/application/component/app/edit-state';
import {createEmptyFile} from 'akeneoassetmanager/domain/model/file';
import Locale from 'akeneoassetmanager/domain/model/locale';
import {channelChanged, localeChanged} from 'akeneoassetmanager/application/action/asset/user';
import LocaleSwitcher from 'akeneoassetmanager/application/component/app/locale-switcher';
import ChannelSwitcher from 'akeneoassetmanager/application/component/app/channel-switcher';
import Channel from 'akeneoassetmanager/domain/model/channel';
import DeleteModal from 'akeneoassetmanager/application/component/app/delete-modal';
import {cancelDeleteModal, openDeleteModal} from 'akeneoassetmanager/application/event/confirmDelete';
import Key from 'akeneoassetmanager/tools/key';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import {getLocales} from 'akeneoassetmanager/application/reducer/structure';
import CompletenessLabel from 'akeneoassetmanager/application/component/app/completeness';
import {canEditAssetFamily} from 'akeneoassetmanager/application/reducer/right';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/product/attribute';
import {redirectToProductGrid} from 'akeneoassetmanager/application/event/router';
import AttributeCode from 'akeneoassetmanager/domain/model/attribute/code';
import {assetFamilyIdentifierStringValue} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {getLabel} from 'pimui/js/i18n';
import EditionAsset, {getEditionAssetCompleteness} from 'akeneoassetmanager/domain/model/asset/edition-asset';
import {getValue} from 'akeneoassetmanager/domain/model/asset/value-collection';
import {isMediaFileData} from 'akeneoassetmanager/domain/model/asset/data/media-file';
import {isMediaLinkData} from 'akeneoassetmanager/domain/model/asset/data/media-link';
import {MediaPreviewType} from 'akeneoassetmanager/tools/media-url-generator';
import {File as FileModel} from 'akeneoassetmanager/domain/model/file';
import {MainMediaThumbnail} from 'akeneoassetmanager/application/component/asset/edit/main-media-thumbnail';

const securityContext = require('pim/security-context');
const routing = require('routing');

interface StateProps {
  sidebar: {
    tabs: Tab[];
    currentTab: string;
  };
  form: {
    isDirty: boolean;
  };
  context: {
    locale: string;
    channel: string;
  };
  rights: {
    asset: {
      edit: boolean;
      delete: boolean;
    };
  };
  asset: EditionAsset;
  structure: {
    locales: Locale[];
    channels: Channel[];
  };
  confirmDelete: {
    isActive: boolean;
  };
  selectedAttribute: NormalizedAttribute | null;
  assetCode: AssetCode;
}

interface DispatchProps {
  events: {
    onSaveEditForm: () => void;
    onLocaleChanged: (locale: Locale) => void;
    onChannelChanged: (channel: Channel) => void;
    onDelete: (asset: EditionAsset) => void;
    onOpenDeleteModal: () => void;
    onCancelDeleteModal: () => void;
    backToAssetFamily: () => void;
    onRedirectToProductGrid: (selectedAttribute: AttributeCode, assetCode: AssetCode) => void;
  };
}

interface EditProps extends StateProps, DispatchProps {}

class AssetEditView extends React.Component<EditProps> {
  public props: EditProps;
  private backToAssetFamily = () => (
    <span
      role="button"
      tabIndex={0}
      className="AknColumn-navigationLink"
      onClick={this.props.events.backToAssetFamily}
      onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
        if (Key.Space === event.key) this.props.events.backToAssetFamily();
      }}
    >
      {__('pim_asset_manager.asset.button.back')}
    </span>
  );

  private onConfirmedDelete = () => {
    const asset = this.props.asset;
    this.props.events.onDelete(asset);
  };

  private getSecondaryActions = (canDelete: boolean): JSX.Element | JSX.Element[] | null => {
    if (canDelete) {
      return (
        <div className="AknSecondaryActions AknDropdown AknButtonList-item">
          <div className="AknSecondaryActions-button dropdown-button" data-toggle="dropdown" />
          <div className="AknDropdown-menu AknDropdown-menu--right">
            <div className="AknDropdown-menuTitle">{__('pim_datagrid.actions.other')}</div>
            <div>
              <button className="AknDropdown-menuLink" onClick={() => this.props.events.onOpenDeleteModal()}>
                {__('pim_asset_manager.asset.button.delete')}
              </button>
            </div>
          </div>
        </div>
      );
    }

    return null;
  };

  render(): JSX.Element | JSX.Element[] {
    const editState = this.props.form.isDirty ? <EditState /> : '';
    const asset = this.props.asset;
    const label = getLabel(asset.labels, this.props.context.locale, asset.code);
    const TabView = sidebarProvider.getView('akeneo_asset_manager_asset_edit', this.props.sidebar.currentTab);
    const completeness = getEditionAssetCompleteness(asset, this.props.context.channel, this.props.context.locale);
    const isUsableSelectedAttributeOnTheGrid =
      null !== this.props.selectedAttribute && true === this.props.selectedAttribute.useable_as_grid_filter;

    return (
      <React.Fragment>
        <div className="AknDefault-contentWithColumn">
          <div className="AknDefault-thirdColumnContainer">
            <div className="AknDefault-thirdColumn" />
          </div>
          <div className="AknDefault-contentWithBottom">
            <div className="AknDefault-mainContent" data-tab={this.props.sidebar.currentTab}>
              <header className="AknTitleContainer">
                <div className="AknTitleContainer-line">
                  <MainMediaThumbnail asset={asset} context={this.props.context} />
                  <div className="AknTitleContainer-mainContainer AknTitleContainer-mainContainer--contained">
                    <div>
                      <div className="AknTitleContainer-line">
                        <div className="AknTitleContainer-breadcrumbs">
                          <Breadcrumb
                            items={[
                              {
                                action: {
                                  type: 'redirect',
                                  route: 'akeneo_asset_manager_asset_family_index',
                                },
                                label: __('pim_asset_manager.asset_family.breadcrumb'),
                              },
                              {
                                action: {
                                  type: 'redirect',
                                  route: 'akeneo_asset_manager_asset_family_edit',
                                  parameters: {
                                    identifier: assetFamilyIdentifierStringValue(asset.assetFamily.identifier),
                                    tab: 'asset',
                                  },
                                },
                                label: assetFamilyIdentifierStringValue(asset.assetFamily.identifier),
                              },
                              {
                                action: {
                                  type: 'display',
                                },
                                label: asset.code,
                              },
                            ]}
                          />
                        </div>
                        <div className="AknTitleContainer-buttonsContainer">
                          <div className="AknTitleContainer-userMenuContainer user-menu">
                            <PimView
                              className={`AknTitleContainer-userMenu ${
                                this.props.rights.asset.edit ? '' : 'AknTitleContainer--withoutMargin'
                              }`}
                              viewName="pim-asset-family-index-user-navigation"
                            />
                          </div>
                          {'product' === this.props.sidebar.currentTab ? (
                            isUsableSelectedAttributeOnTheGrid ? (
                              <div className="AknTitleContainer-actionsContainer AknButtonList">
                                <div className="AknTitleContainer-rightButton">
                                  <button
                                    className="AknButton AknButton--big AknButton--apply AknButton--centered"
                                    onClick={() =>
                                      this.props.events.onRedirectToProductGrid(
                                        (this.props.selectedAttribute as NormalizedAttribute).code,
                                        this.props.assetCode
                                      )
                                    }
                                  >
                                    {__('pim_asset_manager.asset.product.not_enough_items.button')}
                                  </button>
                                </div>
                              </div>
                            ) : null
                          ) : (
                            <div className="AknTitleContainer-actionsContainer AknButtonList">
                              {this.getSecondaryActions(this.props.rights.asset.delete)}
                              {this.props.rights.asset.edit ? (
                                <div className="AknTitleContainer-rightButton">
                                  <button
                                    className="AknButton AknButton--apply"
                                    onClick={this.props.events.onSaveEditForm}
                                  >
                                    {__('pim_asset_manager.asset.button.save')}
                                  </button>
                                </div>
                              ) : null}
                            </div>
                          )}
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
                            channelCode={this.props.context.channel}
                            channels={this.props.structure.channels}
                            locale={this.props.context.locale}
                            className="AknDropdown--right"
                            onChannelChange={this.props.events.onChannelChanged}
                          />
                          <LocaleSwitcher
                            localeCode={this.props.context.locale}
                            locales={this.props.structure.locales}
                            className="AknDropdown--right"
                            onLocaleChange={this.props.events.onLocaleChanged}
                          />
                        </div>
                      </div>
                    </div>
                    {completeness.hasRequiredAttribute() ? (
                      <div>
                        <CompletenessLabel completeness={completeness} />
                      </div>
                    ) : null}
                  </div>
                </div>
              </header>
              <div className="content">
                <TabView code={this.props.sidebar.currentTab} />
              </div>
            </div>
          </div>
          <Sidebar backButton={this.backToAssetFamily} />
        </div>
        {this.props.confirmDelete.isActive && (
          <DeleteModal
            message={__('pim_asset_manager.asset.delete.message', {assetLabel: label})}
            title={__('pim_asset_manager.asset.delete.title')}
            onConfirm={this.onConfirmedDelete}
            onCancel={this.props.events.onCancelDeleteModal}
          />
        )}
      </React.Fragment>
    );
  }
}

export default connect(
  (state: State): StateProps => {
    const tabs = undefined === state.sidebar.tabs ? [] : state.sidebar.tabs;
    const currentTab = undefined === state.sidebar.currentTab ? '' : state.sidebar.currentTab;
    const locale = state.user.catalogLocale;

    return {
      sidebar: {
        tabs,
        currentTab,
      },
      form: {
        isDirty: state.form.state.isDirty,
      },
      context: {
        locale,
        channel: state.user.catalogChannel,
      },
      asset: state.form.data,
      structure: {
        locales: getLocales(state.structure.channels, state.user.catalogChannel),
        channels: state.structure.channels,
      },
      rights: {
        asset: {
          edit:
            securityContext.isGranted('akeneo_assetmanager_asset_edit') &&
            canEditAssetFamily(state.right.assetFamily, state.form.data.assetFamily.identifier),
          delete:
            securityContext.isGranted('akeneo_assetmanager_asset_edit') &&
            securityContext.isGranted('akeneo_assetmanager_asset_delete') &&
            canEditAssetFamily(state.right.assetFamily, state.form.data.assetFamily.identifier),
        },
      },
      confirmDelete: state.confirmDelete,
      selectedAttribute: state.products.selectedAttribute,
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
        onOpenDeleteModal: () => {
          dispatch(openDeleteModal());
        },
        onCancelDeleteModal: () => {
          dispatch(cancelDeleteModal());
        },
        backToAssetFamily: () => {
          dispatch(backToAssetFamily());
        },
        onRedirectToProductGrid: (selectedAttribute: AttributeCode, assetCode: AssetCode) => {
          dispatch(redirectToProductGrid(selectedAttribute, assetCode));
        },
      },
    };
  }
)(AssetEditView);

// TODO Will be trashed when File component will be reworked
export const getEditionAssetMainImageLegacy = (
  asset: EditionAsset,
  channel: ChannelReference,
  locale: LocaleReference
): FileModel => {
  const attributeAsMainMediaIdentifier = asset.assetFamily.attributeAsMainMedia;

  const imageValue = getValue(asset.values, attributeAsMainMediaIdentifier, channel, locale);

  if (undefined === imageValue) {
    return createEmptyFile();
  }

  if (isMediaFileData(imageValue.data)) {
    return imageValue.data;
  }

  if (isMediaLinkData(imageValue.data)) {
    const filePath = routing.generate('akeneo_asset_manager_image_preview', {
      type: MediaPreviewType.Thumbnail,
      attributeIdentifier: imageValue.attribute.identifier,
      data: imageValue.data,
    });
    const originalFilename = '';
    return {
      filePath,
      originalFilename,
    };
  }

  throw Error('attributeAsMainMedia should be either a MediaFile or MediaLink');
};
