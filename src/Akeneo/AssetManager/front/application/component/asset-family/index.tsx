import * as React from 'react';
import {connect} from 'react-redux';
import __ from 'akeneoassetmanager/tools/translator';
import Table from 'akeneoassetmanager/application/component/asset-family/index/table';
import Breadcrumb from 'akeneoassetmanager/application/component/app/breadcrumb';
import AssetFamily from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import PimView from 'akeneoassetmanager/infrastructure/component/pim-view';
import {redirectToAssetFamily} from 'akeneoassetmanager/application/action/asset-family/router';
import {IndexState} from 'akeneoassetmanager/application/reducer/asset-family/index';
import {assetFamilyCreationStart} from 'akeneoassetmanager/domain/event/asset-family/create';
import CreateAssetFamilyModal from 'akeneoassetmanager/application/component/asset-family/create';
// const securityContext = require('pim/security-context');

interface StateProps {
  context: {
    locale: string;
  };

  grid: {
    assetFamilies: AssetFamily[];
    matchesCount: number;
    isLoading: boolean;
  };

  create: {
    active: boolean;
  };

  acls: {
    create: boolean;
  };
}

interface DispatchProps {
  events: {
    onRedirectToAssetFamily: (assetFamily: AssetFamily) => void;
    onCreationStart: () => void;
  };
}
class AssetFamilyListView extends React.Component<StateProps & DispatchProps> {
  private createButton: HTMLButtonElement;

  componentDidMount() {
    if (this.createButton) {
      this.createButton.focus();
    }
  }

  render() {
    const {grid, context, events, create, acls} = this.props;

    return (
      <div className="AknDefault-contentWithColumn">
        <div className="AknDefault-thirdColumnContainer">
          <div className="AknDefault-thirdColumn" />
        </div>
        <div className="AknDefault-contentWithBottom">
          {/* @todo to remove when the feature is available */}
          <div style={{
            display: 'flex',
            fontSize: '15px',
            color: 'rgb(255, 255, 255)',
            minHeight: '50px',
            alignItems: 'center',
            lineHeight: '17px',
            background: 'rgba(189, 10, 10, 0.62)',
            justifyContent: 'center'
          }}>
            <p>The Asset Manager is still in progress. This page is under development. This is a temporary screen which is not supported.</p>
          </div>
          <div className="AknDefault-mainContent">
            <header className="AknTitleContainer">
              <div className="AknTitleContainer-line">
                <div className="AknTitleContainer-mainContainer">
                  <div className="AknTitleContainer-line">
                    <div className="AknTitleContainer-breadcrumbs">
                      <Breadcrumb
                        items={[
                          {
                            action: {
                              type: 'redirect',
                              route: 'akeneo_asset_manager_asset_family_edit',
                            },
                            label: __('pim_asset_manager.asset_family.breadcrumb'),
                          },
                        ]}
                      />
                    </div>
                    <div className="AknTitleContainer-buttonsContainer">
                      <PimView
                        className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
                        viewName="pim-asset-family-index-user-navigation"
                      />
                      {acls.create ? (
                        <div className="AknTitleContainer-actionsContainer AknButtonList">
                          <button
                            type="button"
                            ref={(button: HTMLButtonElement) => {
                              this.createButton = button;
                            }}
                            className="AknButton AknButton--apply AknButtonList-item"
                            onClick={events.onCreationStart}
                          >
                            {__('pim_asset_manager.asset_family.button.create')}
                          </button>
                        </div>
                      ) : null}
                    </div>
                  </div>
                  <div className="AknTitleContainer-line">
                    {/*{grid.isLoading === false && grid.assetFamilies.length === 0 ? (*/}
                      {/*<div className="AknDescriptionHeader AknDescriptionHeader--sticky AknDescriptionHeader--push">*/}
                        {/*<div*/}
                          {/*className="AknDescriptionHeader-icon"*/}
                          {/*style={{*/}
                            {/*backgroundImage: 'mediaLink("/bundles/pimui/images/illustrations/Reference-entities.svg")',*/}
                          {/*}}*/}
                        {/*/>*/}
                        {/*<div className="AknDescriptionHeader-title">*/}
                          {/*{__('pim_asset_manager.asset_family.index.grid.help.title')}*/}
                          {/*<div className="AknDescriptionHeader-description">*/}
                            {/*{__('pim_asset_manager.asset_family.index.grid.help.description_part_one')} <br />*/}
                            {/*{__('pim_asset_manager.asset_family.index.grid.help.description_part_two')} <br />*/}
                            {/*{__('pim_asset_manager.asset_family.index.grid.help.description_part_three')} <br />*/}
                            {/*{__('pim_asset_manager.asset_family.index.grid.help.description_part_four')} <br />*/}
                            {/*<a href="https://help.akeneo.com/pim/articles/what-about-asset-families.html?utm_source=akeneo-app&utm_medium=ref-entities-grid">*/}
                              {/*{__('pim_asset_manager.asset_family.index.grid.help.description_part_five')}*/}
                            {/*</a>*/}
                            {/*<br />*/}
                          {/*</div>*/}
                        {/*</div>*/}
                      {/*</div>*/}
                    {/* ) : ( */}
                      <div className="AknTitleContainer-title">
                        <span className={grid.isLoading ? 'AknLoadingPlaceHolder' : ''}>
                          {__(
                            'pim_asset_manager.asset_family.index.grid.count',
                            {
                              count: grid.assetFamilies.length,
                            },
                            grid.assetFamilies.length
                          )}
                        </span>
                      </div>
                    {/* )} */}
                    <div className="AknTitleContainer-state" />
                  </div>
                </div>
                <div>
                  <div className="AknTitleContainer-line">
                    <div className="AknTitleContainer-context AknButtonList" />
                  </div>
                  <div className="AknTitleContainer-line">
                    <div className="AknTitleContainer-meta AknButtonList" />
                  </div>
                </div>
              </div>
              <div className="AknTitleContainer-line">
                <div className="AknTitleContainer-navigation" />
              </div>
              <div className="AknTitleContainer-line">
                <div className="AknTitleContainer-search" />
              </div>
            </header>
            <div className="AknGrid--gallery">
              <div className="AknGridContainer">
                <Table
                  onRedirectToAssetFamily={events.onRedirectToAssetFamily}
                  locale={context.locale}
                  assetFamilies={grid.assetFamilies}
                  isLoading={grid.isLoading}
                />
              </div>
            </div>
          </div>
        </div>
        {create.active ? <CreateAssetFamilyModal /> : null}
      </div>
    );
  }
}

export default connect(
  (state: IndexState): StateProps => {
    const locale = undefined === state.user || undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;
    const assetFamilies = undefined === state.grid || undefined === state.grid.items ? [] : state.grid.items;
    const matchesCount =
      undefined === state.grid || undefined === state.grid.matchesCount ? 0 : state.grid.matchesCount;

    return {
      context: {
        locale,
      },
      grid: {
        assetFamilies,
        matchesCount,
        isLoading: state.grid.isFetching && state.grid.items.length === 0,
      },
      create: {
        active: state.create.active,
      },
      acls: {
        // create: securityContext.isGranted('akeneo_assetmanager_asset_family_create'),
        create: true,
      },
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onRedirectToAssetFamily: (assetFamilyCreation: AssetFamily) => {
          dispatch(redirectToAssetFamily(assetFamilyCreation.getIdentifier(), 'asset'));
        },
        onCreationStart: () => {
          dispatch(assetFamilyCreationStart());
        },
      },
    };
  }
)(AssetFamilyListView);
