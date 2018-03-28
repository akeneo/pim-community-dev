import * as React from 'react';
import {gridLocaleChanged} from 'pimfront/product-grid/application/action/locale';
import {gridChannelChanged} from 'pimfront/product-grid/application/action/channel';
import {filterChanged} from 'pimfront/product-grid/application/action/filter';
import Locale from 'pimfront/app/domain/model/locale';
import Channel from 'pimfront/app/domain/model/channel';
import LocaleSwitcher from 'pimfront/app/application/component/locale-switcher';
import ChannelSwitcher from 'pimfront/app/application/component/channel-switcher';
import {GlobalState} from 'pimfront/product-grid/application/store/main';
import {connect} from 'react-redux';
import LoadingIndicator from 'pimfront/app/application/component/loading-indicator';
import {NormalizedFilter} from 'pimfront/product-grid/domain/model/filter/filter';
import filterModelProvider from 'pimfront/product-grid/application/configuration/filter-model';
import filterViewProvider from 'pimfront/product-grid/application/configuration/filter-view';
import Filter from 'pimfront/product-grid/domain/model/filter/filter';

interface SidebarDispatch {
  onCatalogLocaleChanged: (locale: Locale) => void;
  onCatalogChannelChanged: (channel: Channel) => void;
  onFilterChanged: (filter: Filter) => void;
}

interface SidebarViewState {
  context: {
    locale: string;
    channel: string;
  };
  locales: Locale[];
  channels: Channel[];
  isFetching: boolean;
  filters: NormalizedFilter[];
}

export const SidebarView = ({
  context,
  channels,
  locales,
  isFetching,
  filters,
  onCatalogLocaleChanged,
  onCatalogChannelChanged,
  onFilterChanged,
}: SidebarViewState & SidebarDispatch) => {
  return (
    <div className="AknColumn">
      <div className="AknColumn-inner">
        <div className="AknColumn-innerTop">
          <LoadingIndicator loading={isFetching} />
          <div className="AknColumn-part">
            <div className="AknColumn-block">
              <ChannelSwitcher
                channelCode={context.channel}
                channels={channels}
                onChannelChange={onCatalogChannelChanged}
              />
            </div>
            <div className="AknColumn-block">
              <LocaleSwitcher localeCode={context.locale} locales={locales} onLocaleChange={onCatalogLocaleChanged} />
            </div>
          </div>
          <div className="AknFilterBox-list">
            <FiltersView filters={filters} locale={context.locale} onFilterChanged={onFilterChanged} />
          </div>
        </div>
      </div>
    </div>
  );
};

interface FilterViewState {
  locale: string;
  filters: NormalizedFilter[];
}

interface FilterDispatch {
  onFilterChanged: (filter: Filter) => void;
}

class FiltersView extends React.Component<FilterViewState & FilterDispatch, FilterViewState> {
  private filterViews: any[] = [];

  componentDidUpdate(nextProps: FilterViewState & FilterDispatch) {
    if (JSON.stringify(this.props.filters) !== JSON.stringify(nextProps.filters)) {
      this.updateFilters(nextProps.filters, nextProps.locale);
    }
  }

  private async updateFilters(filters: NormalizedFilter[], locale: string): Promise<void> {
    this.filterViews = await Promise.all(
      filters.map(async (filter: NormalizedFilter): Promise<any> => {
        const model = await filterModelProvider.getPopulatedFilter(filter);
        const FilterView = await filterViewProvider.getFilter(model);

        return (
          <FilterView
            key={model.field.identifier}
            filter={model}
            onFilterChanged={this.props.onFilterChanged}
            locale={locale}
          />
        );
      })
    );

    this.forceUpdate();
  }

  render() {
    return <div>{this.filterViews}</div>;
  }
}

export const sidebarDecorator = connect(
  (state: GlobalState): SidebarViewState => {
    const localeCode = undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;
    const channelCode = undefined === state.user.catalogChannel ? '' : state.user.catalogChannel;

    const channel: Channel | undefined = state.structure.channels.find(
      (channel: Channel) => channelCode === channel.code
    );
    const locales = undefined !== channel ? channel.locales : [];

    return {
      context: {
        locale: localeCode,
        channel: channelCode,
      },
      locales,
      channels: state.structure.channels,
      isFetching: state.grid.isFetching,
      filters: state.grid.query.filters,
    };
  },
  (dispatch: any): SidebarDispatch => {
    return {
      onCatalogLocaleChanged: (locale: Locale) => {
        dispatch(gridLocaleChanged(locale));
      },
      onCatalogChannelChanged: (channel: Channel) => {
        dispatch(gridChannelChanged(channel));
      },
      onFilterChanged: (filter: Filter) => {
        dispatch(filterChanged(filter));
      },
    };
  }
);

export default sidebarDecorator(SidebarView);
