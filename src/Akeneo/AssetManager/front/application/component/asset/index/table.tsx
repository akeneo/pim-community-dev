import CommonRows from 'akeneoassetmanager/application/component/asset/index/row/common';
import ActionViews from 'akeneoassetmanager/application/component/asset/index/row/action';
import DetailsView from 'akeneoassetmanager/application/component/asset/index/row/detail';
import NoResult from 'akeneoassetmanager/application/component/app/no-result';
import {NormalizedAsset} from 'akeneoassetmanager/domain/model/asset/asset';
import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import AssetFamily from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {Column} from 'akeneoassetmanager/application/reducer/grid';
import {CellViews, FilterViews} from 'akeneoassetmanager/application/component/asset-family/edit/asset';
import {MAX_DISPLAYED_ASSETS} from 'akeneoassetmanager/application/action/asset/search';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {getLabel} from 'pimui/js/i18n';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import {getFilter, getCompletenessFilter, getAttributeFilterKey} from 'akeneoassetmanager/tools/filter';
import SearchField from 'akeneoassetmanager/application/component/asset/index/search-field';
import CompletenessFilter, {
  CompletenessValue,
} from 'akeneoassetmanager/application/component/asset/index/completeness-filter';
import ItemsCounter from 'akeneoassetmanager/application/component/asset/index/items-counter';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';

interface TableState {
  locale: string;
  channel: string;
  grid: {
    assets: NormalizedAsset[];
    columns: Column[];
    matchesCount: number;
    isLoading: boolean;
    page: number;
    filters: Filter[];
  };
  cellViews: CellViews;
  filterViews: FilterViews;
  assetCount: number;
  assetFamily: AssetFamily;
  rights: {
    asset: {
      create: boolean;
      edit: boolean;
      deleteAll: boolean;
      delete: boolean;
    };
  };
}

const columnCollectionsAreDifferent = (firstCollumnCollection: Column[], secondColumnCollection: Column[]): boolean => {
  return !(
    firstCollumnCollection.length === secondColumnCollection.length &&
    firstCollumnCollection.reduce(
      (allEqual: boolean, column: Column, index: number) => allEqual && column === secondColumnCollection[index],
      true
    )
  );
};

export type RowView = React.SFC<{
  isLoading: boolean;
  asset: NormalizedAsset;
  locale: string;
  onRedirectToAsset: (asset: NormalizedAsset) => void;
  onDeleteAsset: (assetCode: AssetCode, label: string) => void;
  position: number;
  columns: Column[];
  cellViews: CellViews;
}>;

interface TableDispatch {
  onRedirectToAsset: (asset: NormalizedAsset) => void;
  onDeleteAsset: (assetCode: AssetCode, label: string) => void;
  onNeedMoreResults: () => void;
  onSearchUpdated: (userSearch: string) => void;
  onFilterUpdated: (filter: Filter) => void;
  onCompletenessFilterUpdated: (completenessValue: CompletenessValue) => void;
}

interface TableProps extends TableState, TableDispatch {}

/**
 * This table is divided in three tables: one on the left to have sticky columns on common properties (common.tsx)
 * On the second table, you will have the additional properties of the assets (details.tsx)
 * On the thrid one, you have all the actions of the asset.
 */
export default class Table extends React.Component<TableProps, {columns: Column[]}> {
  private needResize = false;
  private horizontalScrollContainer: React.RefObject<HTMLDivElement>;
  private verticalScrollContainer: React.RefObject<HTMLDivElement>;
  private detailTable: React.RefObject<HTMLTableElement>;
  private commonTable: React.RefObject<HTMLTableElement>;
  private actionTable: React.RefObject<HTMLTableElement>;
  private columns: Column[] = [];

  constructor(props: TableProps) {
    super(props);

    this.horizontalScrollContainer = React.createRef();
    this.verticalScrollContainer = React.createRef();
    this.detailTable = React.createRef();
    this.commonTable = React.createRef();
    this.actionTable = React.createRef();
  }

  componentDidMount() {
    const detailTable = this.detailTable.current;
    const verticalScrollContainer = this.verticalScrollContainer.current;
    if (
      null !== detailTable &&
      null !== verticalScrollContainer &&
      detailTable.offsetWidth !== verticalScrollContainer.offsetWidth
    ) {
      this.needResize = true;
      window.addEventListener('resize', this.resizeScrollContainer.bind(this));
    }
  }

  componentDidUpdate() {
    if (this.needResize) {
      this.resizeScrollContainer();
    }
    const horizontalScrollContainer = this.horizontalScrollContainer.current;
    if (this.props.grid.page === 0 && null !== horizontalScrollContainer) {
      horizontalScrollContainer.scrollTop = 0;
    }
  }

  componentDidUnMount() {
    if (this.needResize) {
      window.removeEventListener('resize', this.resizeScrollContainer.bind(this));
    }
  }

  resizeScrollContainer() {
    const verticalScrollContainer = this.verticalScrollContainer.current;
    const horizontalScrollContainer = this.horizontalScrollContainer.current;
    const commonTable = this.commonTable.current;
    const detailTable = this.detailTable.current;
    const actionTable = this.actionTable.current;
    if (
      null !== verticalScrollContainer &&
      null !== horizontalScrollContainer &&
      null !== commonTable &&
      null !== detailTable &&
      null !== actionTable
    ) {
      const newWidth = commonTable.offsetWidth + detailTable.offsetWidth + actionTable.offsetWidth;
      const minWidth = horizontalScrollContainer.offsetWidth;
      if (
        newWidth !== verticalScrollContainer.offsetWidth ||
        detailTable.offsetWidth !== verticalScrollContainer.offsetWidth
      ) {
        verticalScrollContainer.style.width = `${newWidth}px`;
        verticalScrollContainer.style.minWidth = `${minWidth}px`;
      }
    }
  }

  handleScroll() {
    const verticalScrollContainer = this.verticalScrollContainer.current;
    const horizontalScrollContainer = this.horizontalScrollContainer.current;
    if (null !== verticalScrollContainer && null !== horizontalScrollContainer) {
      const scrollSize = verticalScrollContainer.offsetHeight;
      const scrollPosition = horizontalScrollContainer.scrollTop;
      const containerSize = horizontalScrollContainer.offsetHeight;
      const remainingHeightToBottom = scrollSize - scrollPosition - containerSize;
      if (remainingHeightToBottom < scrollSize / 3) {
        this.props.onNeedMoreResults();
      }
    }
  }

  getColumnsToDisplay(columns: Column[], channel: string, locale: string) {
    const columnsToDisplay = columns.filter((column: Column) => column.channel === channel && column.locale === locale);
    if (columnCollectionsAreDifferent(columnsToDisplay, this.columns)) {
      this.columns = columnsToDisplay;
    }

    return this.columns;
  }

  render(): JSX.Element | JSX.Element[] {
    const {
      grid,
      locale,
      channel,
      onRedirectToAsset,
      onDeleteAsset,
      onFilterUpdated,
      assetCount,
      cellViews,
      rights,
      filterViews,
    } = this.props;
    const fullTextFilter = getFilter(grid.filters, 'full_text');
    const userSearch = undefined !== fullTextFilter ? fullTextFilter.value : '';
    const completenessValue = getCompletenessFilter(grid.filters);
    const columnsToDisplay = this.getColumnsToDisplay(grid.columns, channel, locale);

    const noResult = 0 === grid.assets.length && false === grid.isLoading;
    const placeholder = 0 === grid.assets.length && grid.isLoading;

    return (
      <React.Fragment>
        <div className="AknFilterBox AknFilterBox--search">
          <div className="AknFilterBox-list filter-box">
            <SearchField value={userSearch} onChange={this.props.onSearchUpdated} changeThreshold={250} />
            <ItemsCounter count={grid.matchesCount} />
            <div className="AknFilterBox-filterContainer AknFilterBox-filterContainer--inline">
              {Object.keys(filterViews).map((attributeCode: AttributeIdentifier) => {
                const View = filterViews[attributeCode].view;
                const attribute = filterViews[attributeCode].attribute;
                const filter = grid.filters.find((filter: Filter) => filter.field === getAttributeFilterKey(attribute));

                return (
                  <div
                    key={attribute.getCode()}
                    className="AknFilterBox-filter AknFilterBox-filter--relative AknFilterBox-filter--smallMargin"
                    data-attribute={attribute.getCode()}
                    data-type={attribute.getType()}
                  >
                    <View attribute={attribute} filter={filter} onFilterUpdated={onFilterUpdated} />
                  </div>
                );
              })}
            </div>
            <CompletenessFilter value={completenessValue} onChange={this.props.onCompletenessFilterUpdated} />
          </div>
        </div>
        {noResult ? (
          <NoResult entityLabel={this.props.assetFamily.getLabel(locale)} />
        ) : (
          <div
            className="AknDefault-horizontalScrollContainer"
            onScroll={this.handleScroll.bind(this)}
            ref={this.horizontalScrollContainer}
          >
            <div className="AknDefault-verticalScrollContainer" ref={this.verticalScrollContainer}>
              <table className="AknGrid AknGrid--light AknGrid--left" ref={this.commonTable}>
                <thead className="AknGrid-header">
                  <tr className="AknGrid-bodyRow">
                    <th className="AknGrid-headerCell">{__('pim_asset_manager.asset.grid.column.image')}</th>
                    <th className="AknGrid-headerCell">{__('pim_asset_manager.asset.grid.column.label')}</th>
                    <th className="AknGrid-headerCell">{__('pim_asset_manager.asset.grid.column.code')}</th>
                    <th className="AknGrid-headerCell">{__('pim_asset_manager.asset.grid.column.complete')}</th>
                  </tr>
                </thead>
                <tbody className="AknGrid-body">
                  <CommonRows
                    assets={grid.assets}
                    locale={locale}
                    placeholder={placeholder}
                    onRedirectToAsset={onRedirectToAsset}
                    assetCount={assetCount}
                  />
                </tbody>
              </table>
              <table className="AknGrid AknGrid--light AknGrid--center" style={{flex: 1}} ref={this.detailTable}>
                <thead className="AknGrid-header">
                  <tr className="AknGrid-bodyRow">
                    {0 === columnsToDisplay.length ? (
                      <th className="AknGrid-headerCell" />
                    ) : (
                      columnsToDisplay.map((column: Column) => {
                        return (
                          <th key={column.key} className="AknGrid-headerCell">
                            {getLabel(column.labels, locale, column.code)}
                          </th>
                        );
                      })
                    )}
                  </tr>
                </thead>
                <tbody className="AknGrid-body">
                  <DetailsView
                    assets={grid.assets}
                    locale={locale}
                    placeholder={placeholder}
                    onRedirectToAsset={onRedirectToAsset}
                    assetCount={assetCount}
                    columns={columnsToDisplay}
                    cellViews={cellViews}
                  />
                </tbody>
              </table>
              <table className="AknGrid AknGrid--light AknGrid--right" ref={this.actionTable}>
                <thead className="AknGrid-header">
                  <tr className="AknGrid-bodyRow">
                    <th className="AknGrid-headerCell AknGrid-headerCell--action" />
                  </tr>
                </thead>
                <tbody className="AknGrid-body">
                  <ActionViews
                    assets={grid.assets}
                    locale={locale}
                    placeholder={placeholder}
                    onRedirectToAsset={onRedirectToAsset}
                    onDeleteAsset={onDeleteAsset}
                    assetCount={assetCount}
                    rights={rights}
                  />
                </tbody>
              </table>
            </div>
            {grid.assets.length >= MAX_DISPLAYED_ASSETS ? (
              <div className="AknDescriptionHeader AknDescriptionHeader--sticky">
                <div
                  className="AknDescriptionHeader-icon"
                  style={{backgroundImage: 'url("/bundles/pimui/images/illustrations/Product.svg")'}}
                />
                <div className="AknDescriptionHeader-title">
                  {__('pim_asset_manager.asset.grid.more_result.title')}
                  <div className="AknDescriptionHeader-description">
                    {__('pim_asset_manager.asset.grid.more_result.description', {total: grid.matchesCount})}
                  </div>
                </div>
              </div>
            ) : null}
          </div>
        )}
      </React.Fragment>
    );
  }
}
