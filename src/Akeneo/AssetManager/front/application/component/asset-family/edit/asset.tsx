import * as React from 'react';
import {connect} from 'react-redux';
import Table from 'akeneoassetmanager/application/component/asset/index/table';
import {NormalizedAsset} from 'akeneoassetmanager/domain/model/asset/asset';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {redirectToAsset} from 'akeneoassetmanager/application/action/asset/router';
import __ from 'akeneoassetmanager/tools/translator';
import {
  getAssetFamilyLabel,
  getAttributeAsMainMedia,
  AssetFamily,
} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import Header from 'akeneoassetmanager/application/component/asset-family/edit/header';
import {assetCreationStart} from 'akeneoassetmanager/domain/event/asset/create';
import {deleteAllAssetFamilyAssets, deleteAsset} from 'akeneoassetmanager/application/action/asset/delete';
import {breadcrumbConfiguration} from 'akeneoassetmanager/application/component/asset-family/edit';
import {
  completenessFilterUpdated,
  filterUpdated,
  needMoreResults,
  searchUpdated,
  updateAssetResults,
} from 'akeneoassetmanager/application/action/asset/search';
import {Column, Filter} from 'akeneoassetmanager/application/reducer/grid';
import AssetFamilyIdentifier, {
  denormalizeAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AssetCode, {denormalizeAssetCode} from 'akeneoassetmanager/domain/model/asset/code';
import DeleteModal from 'akeneoassetmanager/application/component/app/delete-modal';
import {cancelDeleteModal, openDeleteModal} from 'akeneoassetmanager/application/event/confirmDelete';
import {
  CellView,
  FilterView,
  getDataCellView,
  getDataFilterView,
  hasDataFilterView,
} from 'akeneoassetmanager/application/configuration/value';
import Locale from 'akeneoassetmanager/domain/model/locale';
import Channel from 'akeneoassetmanager/domain/model/channel';
import {catalogChannelChanged, catalogLocaleChanged} from 'akeneoassetmanager/domain/event/user';
import {CompletenessValue} from 'akeneoassetmanager/application/component/asset/index/completeness-filter';
import {canEditAssetFamily} from 'akeneoassetmanager/application/reducer/right';
import {Attribute, NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import denormalizeAttribute from 'akeneoassetmanager/application/denormalizer/attribute/attribute';
import {assetUploadStart} from 'akeneoassetmanager/domain/event/asset/upload';
import {MEDIA_FILE_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-file';

const securityContext = require('pim/security-context');

interface StateProps {
  context: {
    locale: string;
    channel: string;
  };
  assetFamily: AssetFamily;
  grid: {
    assets: NormalizedAsset[];
    columns: Column[];
    matchesCount: number;
    totalCount: number;
    isLoading: boolean;
    page: number;
    filters: Filter[];
  };
  attributes: NormalizedAttribute[] | null;
  rights: {
    asset: {
      upload: boolean;
      create: boolean;
      edit: boolean;
      deleteAll: boolean;
      delete: boolean;
    };
  };
  confirmDelete: {
    isActive: boolean;
    identifier?: string;
    label?: string;
  };
}

interface DispatchProps {
  events: {
    onRedirectToAsset: (asset: NormalizedAsset) => void;
    onDeleteAsset: (assetFamilyIdentifier: AssetFamilyIdentifier, assetCode: AssetCode) => void;
    onNeedMoreResults: () => void;
    onSearchUpdated: (userSearch: string) => void;
    onFilterUpdated: (filter: Filter) => void;
    onLocaleChanged: (locale: Locale) => void;
    onChannelChanged: (locale: Channel) => void;
    onCompletenessFilterUpdated: (completenessValue: CompletenessValue) => void;
    onDeleteAllAssets: (assetFamily: AssetFamily) => void;
    onAssetCreationStart: () => void;
    onFirstLoad: () => void;
    onOpenDeleteAllAssetsModal: () => void;
    onAssetUploadStart: () => void;
    onOpenDeleteAssetModal: (assetCode: AssetCode, label: string) => void;
    onCancelDeleteModal: () => void;
  };
}

export type CellViews = {
  [key: string]: CellView;
};

export type FilterViews = {
  [key: string]: {
    view: FilterView;
    attribute: Attribute;
  };
};

const SecondaryActions = ({
  canDeleteAllAssets,
  canUploadAsset,
  onOpenDeleteAllAssetsModal,
  onStartMassUpload,
}: {
  onOpenDeleteAllAssetsModal: () => void;
  onStartMassUpload: () => void;
  canDeleteAllAssets: boolean;
  canUploadAsset: boolean;
}) => {
  if (!canDeleteAllAssets && !canUploadAsset) return null;

  return (
    <>
      <div className="AknSecondaryActions AknDropdown AknButtonList-item">
        <div className="AknSecondaryActions-button dropdown-button" data-toggle="dropdown" />
        <div className="AknDropdown-menu AknDropdown-menu--right">
          <div className="AknDropdown-menuTitle">{__('pim_datagrid.actions.other')}</div>
          <div>
            {canDeleteAllAssets && (
              <button tabIndex={-1} className="AknDropdown-menuLink" onClick={() => onOpenDeleteAllAssetsModal()}>
                {__('pim_asset_manager.asset.button.delete_all')}
              </button>
            )}
            {canUploadAsset && (
              <button tabIndex={-1} className="AknDropdown-menuLink" onClick={() => onStartMassUpload()}>
                {__('pim_asset_manager.asset.button.mass_upload')}
              </button>
            )}
          </div>
        </div>
      </div>
    </>
  );
};

class Assets extends React.Component<StateProps & DispatchProps, {cellViews: CellViews; filterViews: FilterViews}> {
  state = {cellViews: {}, filterViews: {}};

  componentDidMount() {
    this.props.events.onFirstLoad();
  }

  static getDerivedStateFromProps(
    props: StateProps & DispatchProps,
    {cellViews, filterViews}: {cellViews: CellViews; filterViews: FilterViews}
  ) {
    let needToUpdateState = false;
    let newCellViews = cellViews;
    let newFilterViews = filterViews;

    if (0 === Object.keys(cellViews).length && 0 !== props.grid.columns.length) {
      newCellViews = props.grid.columns.reduce((cellViews: CellViews, column: Column): CellViews => {
        cellViews[column.key] = getDataCellView(column.type);

        return cellViews;
      }, {});

      needToUpdateState = true;
    }

    if (0 === Object.keys(filterViews).length && null !== props.attributes) {
      newFilterViews = props.attributes.reduce((filters: FilterViews, normalizedAttribute: NormalizedAttribute) => {
        const attribute = denormalizeAttribute(normalizedAttribute);

        if (hasDataFilterView(attribute.type)) {
          filters[attribute.getCode()] = {
            view: getDataFilterView(attribute.type),
            attribute,
          };
        }

        return filters;
      }, {});

      needToUpdateState = true;
    }

    if (!needToUpdateState) {
      return null;
    }

    return {
      cellViews: newCellViews,
      filterViews: newFilterViews,
    };
  }

  render() {
    const {context, grid, events, assetFamily, rights, confirmDelete} = this.props;

    return (
      <React.Fragment>
        <Header
          label={getAssetFamilyLabel(assetFamily, context.locale)}
          image={assetFamily.image}
          primaryAction={() => {
            return rights.asset.create ? (
              <button className="AknButton AknButton--action" onClick={events.onAssetCreationStart}>
                {__('pim_asset_manager.asset.button.create')}
              </button>
            ) : null;
          }}
          secondaryActions={() => {
            return (
              <SecondaryActions
                onOpenDeleteAllAssetsModal={() => {
                  events.onOpenDeleteAllAssetsModal();
                }}
                onStartMassUpload={() => {
                  events.onAssetUploadStart();
                }}
                canDeleteAllAssets={rights.asset.deleteAll}
                canUploadAsset={rights.asset.upload}
              />
            );
          }}
          withLocaleSwitcher={true}
          withChannelSwitcher={true}
          isDirty={false}
          isLoading={grid.isLoading}
          breadcrumbConfiguration={breadcrumbConfiguration}
          onLocaleChanged={events.onLocaleChanged}
          onChannelChanged={events.onChannelChanged}
          displayActions={this.props.rights.asset.create || this.props.rights.asset.deleteAll}
        />
        {0 !== grid.totalCount ? (
          <Table
            onRedirectToAsset={events.onRedirectToAsset}
            onDeleteAsset={events.onOpenDeleteAssetModal}
            onNeedMoreResults={events.onNeedMoreResults}
            onSearchUpdated={events.onSearchUpdated}
            onFilterUpdated={events.onFilterUpdated}
            onCompletenessFilterUpdated={events.onCompletenessFilterUpdated}
            assetCount={grid.matchesCount}
            locale={context.locale}
            channel={context.channel}
            grid={grid}
            cellViews={this.state.cellViews}
            filterViews={this.state.filterViews}
            assetFamily={assetFamily}
            rights={rights}
          />
        ) : (
          <div className="AknGridContainer-noData">
            <div className="AknGridContainer-noDataImage AknGridContainer-noDataImage--asset-family" />
            <div className="AknGridContainer-noDataTitle">
              {__('pim_asset_manager.asset.no_data.title', {
                entityLabel: getAssetFamilyLabel(assetFamily, context.locale),
              })}
            </div>
            <div className="AknGridContainer-noDataSubtitle">{__('pim_asset_manager.asset.no_data.subtitle')}</div>
          </div>
        )}
        {confirmDelete.isActive && undefined === confirmDelete.identifier && (
          <DeleteModal
            message={__('pim_asset_manager.asset.delete_all.confirm', {
              entityIdentifier: assetFamily.identifier,
            })}
            title={__('pim_asset_manager.asset.delete.title')}
            onConfirm={() => {
              events.onDeleteAllAssets(assetFamily);
            }}
            onCancel={events.onCancelDeleteModal}
          />
        )}
        {confirmDelete.isActive && undefined !== confirmDelete.identifier && (
          <DeleteModal
            message={__('pim_asset_manager.asset.delete.message', {
              assetLabel: confirmDelete.label,
            })}
            title={__('pim_asset_manager.asset.delete.title')}
            onConfirm={() => {
              events.onDeleteAsset(assetFamily.identifier, denormalizeAssetCode(confirmDelete.identifier as string));
            }}
            onCancel={events.onCancelDeleteModal}
          />
        )}
      </React.Fragment>
    );
  }
}

export default connect(
  (state: EditState): StateProps => {
    const assetFamily = state.form.data;
    const assets = undefined === state.grid || undefined === state.grid.items ? [] : state.grid.items;
    const page = undefined === state.grid || undefined === state.grid.query.page ? 0 : state.grid.query.page;
    const filters = undefined === state.grid || undefined === state.grid.query.filters ? [] : state.grid.query.filters;
    const columns =
      undefined === state.grid || undefined === state.grid.query || undefined === state.grid.query.columns
        ? []
        : state.grid.query.columns;
    const matchesCount =
      undefined === state.grid || undefined === state.grid.matchesCount ? 0 : state.grid.matchesCount;

    const canCreateAsset =
      securityContext.isGranted('akeneo_assetmanager_asset_create') &&
      canEditAssetFamily(state.right.assetFamily, state.form.data.identifier);
    const attributeAsMainMedia = getAttributeAsMainMedia(assetFamily);
    const canUploadAsset = canCreateAsset && attributeAsMainMedia.type === MEDIA_FILE_ATTRIBUTE_TYPE;

    return {
      context: {
        locale: state.user.catalogLocale,
        channel: state.user.catalogChannel,
      },
      assetFamily,
      grid: {
        assets,
        matchesCount,
        totalCount: state.grid.totalCount,
        columns,
        isLoading: state.grid.isFetching,
        page,
        filters,
      },
      attributes: state.attributes.attributes,
      rights: {
        asset: {
          upload: canUploadAsset,
          create: canCreateAsset,
          edit:
            securityContext.isGranted('akeneo_assetmanager_asset_edit') &&
            canEditAssetFamily(state.right.assetFamily, state.form.data.identifier),
          deleteAll:
            securityContext.isGranted('akeneo_assetmanager_asset_create') &&
            securityContext.isGranted('akeneo_assetmanager_asset_edit') &&
            securityContext.isGranted('akeneo_assetmanager_assets_delete_all') &&
            canEditAssetFamily(state.right.assetFamily, state.form.data.identifier),
          delete:
            securityContext.isGranted('akeneo_assetmanager_asset_create') &&
            securityContext.isGranted('akeneo_assetmanager_asset_edit') &&
            securityContext.isGranted('akeneo_assetmanager_asset_delete') &&
            canEditAssetFamily(state.right.assetFamily, state.form.data.identifier),
        },
      },
      confirmDelete: state.confirmDelete,
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onRedirectToAsset: (asset: NormalizedAsset) => {
          dispatch(
            redirectToAsset(
              denormalizeAssetFamilyIdentifier(asset.asset_family_identifier),
              denormalizeAssetCode(asset.code)
            )
          );
        },
        onDeleteAsset: (assetFamilyIdentifier: AssetFamilyIdentifier, assetCode: AssetCode) => {
          dispatch(deleteAsset(assetFamilyIdentifier, assetCode, true));
        },
        onNeedMoreResults: () => {
          dispatch(needMoreResults());
        },
        onSearchUpdated: (userSearch: string) => {
          dispatch(searchUpdated(userSearch));
        },
        onFilterUpdated: (filter: Filter) => {
          dispatch(filterUpdated(filter));
        },
        onCompletenessFilterUpdated: (completenessValue: CompletenessValue) => {
          dispatch(completenessFilterUpdated(completenessValue));
        },
        onAssetCreationStart: () => {
          dispatch(assetCreationStart());
        },
        onAssetUploadStart: () => {
          dispatch(assetUploadStart());
        },
        onDeleteAllAssets: (assetFamily: AssetFamily) => {
          dispatch(deleteAllAssetFamilyAssets(assetFamily));
        },
        onCancelDeleteModal: () => {
          dispatch(cancelDeleteModal());
        },
        onOpenDeleteAllAssetsModal: () => {
          dispatch(openDeleteModal());
        },
        onOpenDeleteAssetModal: (assetCode: AssetCode, label: string) => {
          dispatch(openDeleteModal(assetCode, label));
        },
        onLocaleChanged: (locale: Locale) => {
          dispatch(catalogLocaleChanged(locale.code));
          dispatch(updateAssetResults(false));
        },
        onChannelChanged: (channel: Channel) => {
          dispatch(catalogChannelChanged(channel.code));
          dispatch(updateAssetResults(false));
        },
        onFirstLoad: () => {
          dispatch(updateAssetResults(false));
        },
      },
    };
  }
)(Assets);

interface AssetLabelProps {
  grid: {
    totalCount: number;
  };
}

class AssetLabel extends React.Component<AssetLabelProps> {
  render() {
    const {grid} = this.props;

    return (
      <React.Fragment>
        {__('pim_asset_manager.asset_family.tab.assets')}
        <span>&nbsp;</span>
        <span className="AknColumn-span">({grid.totalCount})</span>
      </React.Fragment>
    );
  }
}

export const label = connect(
  (state: EditState): AssetLabelProps => {
    return {
      grid: {
        totalCount: state.grid.totalCount,
      },
    };
  },
  () => {
    return {};
  }
)(AssetLabel);
