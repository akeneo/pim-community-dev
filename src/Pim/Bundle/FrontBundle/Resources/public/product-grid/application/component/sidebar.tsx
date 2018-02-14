import * as React from 'react';
import {gridLocaleChanged} from 'pimfront/product-grid/application/action/locale';
import {gridChannelChanged} from 'pimfront/product-grid/application/action/channel';
import Locale from 'pimfront/app/domain/model/locale';
import Channel from 'pimfront/app/domain/model/channel';
import LocaleSwitcher from 'pimfront/app/application/component/locale-switcher';
import ChannelSwitcher from 'pimfront/app/application/component/channel-switcher';
import IdentifierFilter from 'pimfront/product-grid/application/component/filter/identifier';
import {GlobalState} from 'pimfront/product-grid/application/store/main';
import {connect} from 'react-redux';
import LoadingIndicator from 'pimfront/app/application/component/loading-indicator';

interface SidebarDispatch {
  onCatalogLocaleChanged: (locale: Locale) => void;
  onCatalogChannelChanged: (channel: Channel) => void;
}

interface SidebarViewState {
  context: {
    locale: string;
    channel: string;
  };
  structure: {
    locales: Locale[];
    channels: Channel[];
  };
  isFetching: boolean;
}

export const SidebarView = ({
  context,
  structure,
  isFetching,
  onCatalogLocaleChanged,
  onCatalogChannelChanged,
}: SidebarViewState & SidebarDispatch) => {
  const channel: Channel | undefined = structure.channels.find((channel: Channel) => context.channel === channel.code);
  const locales = undefined !== channel ? channel.locales : [];

  return (
    <div className="AknColumn">
      <div className="AknColumn-inner">
        <div className="AknColumn-innerTop">
          <LoadingIndicator loading={isFetching} />
          <div className="AknColumn-part">
            <div className="AknColumn-block">
              <ChannelSwitcher
                channelCode={context.channel}
                channels={structure.channels}
                onChannelChange={onCatalogChannelChanged}
              />
            </div>
            <div className="AknColumn-block">
              <LocaleSwitcher localeCode={context.locale} locales={locales} onLocaleChange={onCatalogLocaleChanged} />
            </div>
          </div>
          <div className="AknFilterBox-list">
            <IdentifierFilter />
          </div>
        </div>
      </div>
    </div>
  );
};

export const sidebarDecorator = connect(
  (state: GlobalState): SidebarViewState => {
    const locale = undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;
    const channel = undefined === state.user.catalogChannel ? '' : state.user.catalogChannel;

    return {
      context: {
        locale,
        channel,
      },
      structure: {
        locales: state.structure.locales,
        channels: state.structure.channels,
      },
      isFetching: state.grid.isFetching,
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
