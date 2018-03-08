import * as React from 'react';
import {gridLocaleChanged} from 'pimfront/product-grid/application/action/locale';
import {gridChannelChanged} from 'pimfront/product-grid/application/action/channel';
import Locale from 'pimfront/app/domain/model/locale';
import Channel from 'pimfront/app/domain/model/channel';
import LocaleSwitcher from 'pimfront/app/application/component/locale-switcher';
import ChannelSwitcher from 'pimfront/app/application/component/channel-switcher';
import {GlobalState} from 'pimfront/product-grid/application/store/main';
import {connect} from 'react-redux';
import LoadingIndicator from 'pimfront/app/application/component/loading-indicator';
import StatusFilterModel from 'pimfront/product-grid/domain/model/filter/property/status';
import BooleanFilterView from 'pimfront/product-grid/application/component/filter/boolean';
import {Property} from 'pimfront/product-grid/domain/model/field';
import {NormalizedFilter} from 'pimfront/product-grid/domain/model/filter/filter';

interface SidebarDispatch {
  onCatalogLocaleChanged: (locale: Locale) => void;
  onCatalogChannelChanged: (channel: Channel) => void;
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
  onCatalogLocaleChanged,
  onCatalogChannelChanged,
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
            <BooleanFilterView
              filter={StatusFilterModel.createEmptyFromProperty(
                Property.createFromProperty({identifier: 'enabled', label: 'Status'})
              )}
            />
          </div>
        </div>
      </div>
    </div>
  );
};

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
    };
  }
);

export default sidebarDecorator(SidebarView);
