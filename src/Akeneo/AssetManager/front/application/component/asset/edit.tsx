import * as React from 'react';
import {connect} from 'react-redux';
import {EditState as State} from 'akeneoassetmanager/application/reducer/asset/edit';
import Sidebar from 'akeneoassetmanager/application/component/app/sidebar';
import {Tab} from 'akeneoassetmanager/application/reducer/sidebar';
import sidebarProvider from 'akeneoassetmanager/application/configuration/sidebar';
import Breadcrumb from 'akeneoassetmanager/application/component/app/breadcrumb';
import Image from 'akeneoassetmanager/application/component/app/image';
import __ from 'akeneoassetmanager/tools/translator';
import PimView from 'akeneoassetmanager/infrastructure/component/pim-view';
import Asset, {NormalizedAsset} from 'akeneoassetmanager/domain/model/asset/asset';
import {saveAsset, assetImageUpdated, backToAssetFamily} from 'akeneoassetmanager/application/action/asset/edit';
import {deleteAsset} from 'akeneoassetmanager/application/action/asset/delete';
import EditState from 'akeneoassetmanager/application/component/app/edit-state';
import File from 'akeneoassetmanager/domain/model/file';
import Locale from 'akeneoassetmanager/domain/model/locale';
import {localeChanged, channelChanged} from 'akeneoassetmanager/application/action/asset/user';
import LocaleSwitcher from 'akeneoassetmanager/application/component/app/locale-switcher';
import ChannelSwitcher from 'akeneoassetmanager/application/component/app/channel-switcher';
import denormalizeAsset from 'akeneoassetmanager/application/denormalizer/asset';
import Channel from 'akeneoassetmanager/domain/model/channel';
import DeleteModal from 'akeneoassetmanager/application/component/app/delete-modal';
import {openDeleteModal, cancelDeleteModal} from 'akeneoassetmanager/application/event/confirmDelete';
import Key from 'akeneoassetmanager/tools/key';
import {denormalizeLocaleReference} from 'akeneoassetmanager/domain/model/locale-reference';
import {denormalizeChannelReference} from 'akeneoassetmanager/domain/model/channel-reference';
import {getLocales} from 'akeneoassetmanager/application/reducer/structure';
import CompletenessLabel from 'akeneoassetmanager/application/component/app/completeness';
import {canEditAssetFamily} from 'akeneoassetmanager/application/reducer/right';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/product/attribute';
import {redirectToProductGrid} from 'akeneoassetmanager/application/event/router';
import AttributeCode from 'akeneoassetmanager/domain/model/attribute/code';
import {assetFamilyIdentifierStringValue} from 'akeneoassetmanager/domain/model/asset-family/identifier';

// const securityContext = require('pim/security-context');

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
  asset: NormalizedAsset;
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
    onImageUpdated: (image: File) => void;
    onDelete: (asset: Asset) => void;
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
    const asset = denormalizeAsset(this.props.asset);
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
    const asset = denormalizeAsset(this.props.asset);
    const label = asset.getLabel(this.props.context.locale);
    const TabView = sidebarProvider.getView('akeneo_asset_manager_asset_edit', this.props.sidebar.currentTab);
    const completeness = asset.getCompleteness(
      denormalizeChannelReference(this.props.context.channel),
      denormalizeLocaleReference(this.props.context.locale)
    );
    const isUsableSelectedAttributeOnTheGrid =
      null !== this.props.selectedAttribute && true === this.props.selectedAttribute.useable_as_grid_filter;

    return (
      <React.Fragment>
        <div className="AknDefault-contentWithColumn">
          <div className="AknDefault-thirdColumnContainer">
            <div className="AknDefault-thirdColumn" />
          </div>
          <div className="AknDefault-contentWithBottom">
            {/* @todo to remove when the feature is available */}
            <div
              style={{
                display: 'flex',
                fontSize: '15px',
                color: 'rgb(255, 255, 255)',
                minHeight: '50px',
                alignItems: 'center',
                lineHeight: '17px',
                background: 'rgba(189, 10, 10, 0.62)',
                justifyContent: 'center',
              }}
            >
              <p>
                The Asset Manager is still in progress. This page is under development. This is a temporary screen which
                is not supported.
              </p>
            </div>
            <div className="AknDefault-mainContent" data-tab={this.props.sidebar.currentTab}>
              <header className="AknTitleContainer">
                <div className="AknTitleContainer-line">
                  <Image
                    alt={__('pim_asset_manager.asset.img', {'{{ label }}': label})}
                    image={asset.getImage()}
                    readOnly={true}
                  />
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
                                    identifier: assetFamilyIdentifierStringValue(asset.getAssetFamilyIdentifier()),
                                    tab: 'asset',
                                  },
                                },
                                label: assetFamilyIdentifierStringValue(asset.getAssetFamilyIdentifier()),
                              },
                              {
                                action: {
                                  type: 'display',
                                },
                                label: asset.getCode(),
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
            // securityContext.isGranted('akeneo_assetmanager_asset_edit') &&
            canEditAssetFamily(state.right.assetFamily, state.form.data.asset_family_identifier),
          delete:
            // securityContext.isGranted('akeneo_assetmanager_asset_edit') &&
            // securityContext.isGranted('akeneo_assetmanager_asset_delete') &&
            canEditAssetFamily(state.right.assetFamily, state.form.data.asset_family_identifier),
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
        onImageUpdated: (image: File) => {
          dispatch(assetImageUpdated(image));
        },
        onDelete: (asset: Asset) => {
          dispatch(deleteAsset(asset.getAssetFamilyIdentifier(), asset.getCode()));
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
