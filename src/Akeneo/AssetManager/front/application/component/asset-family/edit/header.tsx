import React, {useRef} from 'react';
import EditState from 'akeneoassetmanager/application/component/app/edit-state';
import {connect} from 'react-redux';
import LocaleSwitcher from 'akeneoassetmanager/application/component/app/locale-switcher';
import PimView from 'akeneoassetmanager/infrastructure/component/pim-view';
import {File} from 'akeneoassetmanager/domain/model/file';
import Locale from 'akeneoassetmanager/domain/model/locale';
import {EditState as State} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {catalogLocaleChanged, catalogChannelChanged} from 'akeneoassetmanager/domain/event/user';
import Channel from 'akeneoassetmanager/domain/model/channel';
import ChannelSwitcher from 'akeneoassetmanager/application/component/app/channel-switcher';
import {getLocales} from 'akeneoassetmanager/application/reducer/structure';
import {useAutoFocus} from 'akeneo-design-system';

interface OwnProps {
  label: string;
  image: File | null;
  primaryAction: (defaultFocus: React.RefObject<any>) => JSX.Element | null;
  secondaryActions?: () => JSX.Element | null;
  withLocaleSwitcher: boolean;
  withChannelSwitcher: boolean;
  isDirty: boolean;
  isLoading?: boolean;
  displayActions?: boolean; // @todo : It will be mandatory (more convenience for the spike)
  breadcrumb: React.ReactNode;
  onLocaleChanged?: (locale: Locale) => void;
  onChannelChanged?: (channel: Channel) => void;
}

interface StateProps extends OwnProps {
  context: {
    locale: string;
    channel: string;
  };
  structure?: {
    locales: Locale[];
    channels: Channel[];
  };
}

interface DispatchProps {
  events?: {
    onLocaleChanged: (locale: Locale) => void;
    onChannelChanged: (channel: Channel) => void;
  };
}

interface HeaderProps extends StateProps, DispatchProps {}

const HeaderView = ({
  label,
  primaryAction,
  secondaryActions,
  withChannelSwitcher,
  withLocaleSwitcher,
  isDirty,
  isLoading,
  displayActions = true,
  breadcrumb,
  context,
  structure,
  events,
}: HeaderProps) => {
  const saveButtonRef = useRef(null);
  useAutoFocus(saveButtonRef);

  return (
    <header className="AknTitleContainer">
      <div className="AknTitleContainer-line">
        <div className="AknTitleContainer-mainContainer">
          <div>
            <div className="AknTitleContainer-line">
              <div className="AknTitleContainer-breadcrumbs">{breadcrumb}</div>
              <div className="AknTitleContainer-buttonsContainer">
                <div className={`AknLoadingIndicator ${true === isLoading ? '' : 'AknLoadingIndicator--hidden'}`} />
                <div className="AknTitleContainer-userMenuContainer user-menu">
                  <PimView
                    className={`AknTitleContainer-userMenu ${displayActions ? '' : 'AknTitleContainer--withoutMargin'}`}
                    viewName="pim-asset-family-index-user-navigation"
                  />
                </div>
                <div className="AknTitleContainer-actionsContainer AknButtonList">
                  {secondaryActions && secondaryActions()}
                  <div className="AknTitleContainer-rightButton">{primaryAction(saveButtonRef)}</div>
                </div>
              </div>
            </div>
            <div className="AknTitleContainer-line">
              <div className="AknTitleContainer-title">{label}</div>
              {isDirty ? <EditState /> : null}
            </div>
          </div>
          <div>
            <div className="AknTitleContainer-line">
              <div className="AknTitleContainer-context AknButtonList">
                {withChannelSwitcher && structure && events ? (
                  <ChannelSwitcher
                    channelCode={context.channel}
                    channels={structure.channels}
                    locale={context.locale}
                    onChannelChange={events.onChannelChanged}
                    className="AknDropdown--right"
                  />
                ) : null}
                {withLocaleSwitcher && structure && events ? (
                  <LocaleSwitcher
                    localeCode={context.locale}
                    locales={structure.locales}
                    onLocaleChange={events.onLocaleChanged}
                    className="AknDropdown--right"
                  />
                ) : null}
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>
  );
};

export default connect(
  (state: State, ownProps: OwnProps): StateProps => {
    return {
      ...ownProps,
      context: {
        locale: state.user.catalogLocale,
        channel: state.user.catalogChannel,
      },
      structure: {
        locales: ownProps.withChannelSwitcher
          ? getLocales(state.structure.channels, state.user.catalogChannel)
          : state.structure.locales,
        channels: state.structure.channels,
      },
    };
  },
  (dispatch: any, ownProps: OwnProps): DispatchProps => {
    return {
      events: {
        onLocaleChanged:
          undefined === ownProps.onLocaleChanged
            ? (locale: Locale) => {
                dispatch(catalogLocaleChanged(locale.code));
              }
            : ownProps.onLocaleChanged,
        onChannelChanged:
          undefined === ownProps.onChannelChanged
            ? (channel: Channel) => {
                dispatch(catalogChannelChanged(channel.code));
              }
            : ownProps.onChannelChanged,
      },
    };
  }
)(HeaderView);
export {HeaderView};
