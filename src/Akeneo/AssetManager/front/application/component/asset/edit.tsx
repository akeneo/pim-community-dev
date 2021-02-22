import * as React from 'react';
import {connect} from 'react-redux';
import {EditState as State} from 'akeneoassetmanager/application/reducer/asset/edit';
import sidebarProvider from 'akeneoassetmanager/application/configuration/sidebar';
import Breadcrumb from 'akeneoassetmanager/application/component/app/breadcrumb';
import __ from 'akeneoassetmanager/tools/translator';
import PimView from 'akeneoassetmanager/infrastructure/component/pim-view';
import {saveAsset} from 'akeneoassetmanager/application/action/asset/edit';
import {deleteAsset} from 'akeneoassetmanager/application/action/asset/delete';
import EditState from 'akeneoassetmanager/application/component/app/edit-state';
import Locale from 'akeneoassetmanager/domain/model/locale';
import {channelChanged, localeChanged} from 'akeneoassetmanager/application/action/asset/user';
import LocaleSwitcher from 'akeneoassetmanager/application/component/app/locale-switcher';
import ChannelSwitcher from 'akeneoassetmanager/application/component/app/channel-switcher';
import Channel from 'akeneoassetmanager/domain/model/channel';
import DeleteModal from 'akeneoassetmanager/application/component/app/delete-modal';
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
import {MainMediaThumbnail} from 'akeneoassetmanager/application/component/asset/edit/main-media-thumbnail';
import {redirectToAssetFamilyListItem} from 'akeneoassetmanager/application/action/asset-family/router';
import styled from "styled-components";
const securityContext = require('pim/security-context');

const CodeAsLabel = styled.div `
     ::first-letter {
     text-transform: initial !important;
   }
`

const Label : React.FC<{isCode:boolean}> = ({children, isCode}) => (isCode
        ? <CodeAsLabel className="AknTitleContainer-title">{children}</CodeAsLabel>
        : <div className="AknTitleContainer-title">{children}</div>)

interface StateProps {
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
  selectedAttribute: NormalizedAttribute | null;
  assetCode: AssetCode;
}

interface DispatchProps {
  events: {
    onSaveEditForm: () => void;
    onLocaleChanged: (locale: Locale) => void;
    onChannelChanged: (channel: Channel) => void;
    onDelete: (asset: EditionAsset) => void;
    backToAssetFamilyList: () => void;
    onRedirectToProductGrid: (selectedAttribute: AttributeCode, assetCode: AssetCode) => void;
  };
}

interface EditProps extends StateProps, DispatchProps {}

class AssetEditView extends React.Component<EditProps> {
  public props: EditProps;
  public state: {isDeleteModalOpen: boolean} = {
    isDeleteModalOpen: false,
  };

  private onConfirmedDelete = () => {
    const asset = this.props.asset;
    this.props.events.onDelete(asset);
    this.setState({isDeleteModalOpen: false});
  };

  private getSecondaryActions = (canDelete: boolean): JSX.Element | JSX.Element[] | null => {
    if (canDelete) {
      return (
        <div className="AknSecondaryActions AknDropdown AknButtonList-item">
          <div className="AknSecondaryActions-button dropdown-button" data-toggle="dropdown" />
          <div className="AknDropdown-menu AknDropdown-menu--right">
            <div className="AknDropdown-menuTitle">{__('pim_datagrid.actions.other')}</div>
            <div>
              <button className="AknDropdown-menuLink" onClick={() => this.setState({isDeleteModalOpen: true})}>
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
    const TabView = sidebarProvider.getView('akeneo_asset_manager_asset_edit', 'enrich');
    const completeness = getEditionAssetCompleteness(asset, this.props.context.channel, this.props.context.locale);

    return (
      <React.Fragment>
        <div className="AknDefault-contentWithColumn">
          <div className="AknDefault-thirdColumnContainer">
            <div className="AknDefault-thirdColumn" />
          </div>
          <div className="AknDefault-contentWithBottom">
            <div className="AknDefault-mainContent" data-tab="enrich">
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
                                    tab: 'attribute',
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
                        </div>
                      </div>
                      <div className="AknTitleContainer-line">
                        <Label isCode={label===`[${asset.code}]`}>{label}</Label>
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
                <TabView code="enrich" />
              </div>
            </div>
          </div>
        </div>
        {this.state.isDeleteModalOpen && (
          <DeleteModal
            message={__('pim_asset_manager.asset.delete.message', {assetLabel: label})}
            title={__('pim_asset_manager.asset.delete.title')}
            onConfirm={this.onConfirmedDelete}
            onCancel={() => this.setState({isDeleteModalOpen: false})}
          />
        )}
      </React.Fragment>
    );
  }
}

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
        backToAssetFamilyList: () => {
          dispatch(redirectToAssetFamilyListItem());
        },
        onRedirectToProductGrid: (selectedAttribute: AttributeCode, assetCode: AssetCode) => {
          dispatch(redirectToProductGrid(selectedAttribute, assetCode));
        },
      },
    };
  }
)(AssetEditView);
