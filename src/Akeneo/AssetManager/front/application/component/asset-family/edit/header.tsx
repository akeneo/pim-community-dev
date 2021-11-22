import React, {useRef, ReactNode} from 'react';
import {connect} from 'react-redux';
import {useAutoFocus} from 'akeneo-design-system';
import {Channel, ChannelCode, Locale, LocaleCode, LocaleSelector, PimView} from '@akeneo-pim-community/shared';
import EditState from 'akeneoassetmanager/application/component/app/edit-state';
import {File} from 'akeneoassetmanager/domain/model/file';
import {EditState as State} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {catalogLocaleChanged, catalogChannelChanged} from 'akeneoassetmanager/domain/event/user';
import {ChannelSwitcher} from 'akeneoassetmanager/application/component/app/channel-switcher';
import {getLocales} from 'akeneoassetmanager/application/reducer/structure';
import {ButtonContainer} from 'akeneoassetmanager/application/component/app/button';

interface OwnProps {
  label: string;
  image: File | null;
  primaryAction: (defaultFocus: React.RefObject<any>) => JSX.Element | null;
  secondaryActions?: ReactNode;
  withLocaleSwitcher: boolean;
  withChannelSwitcher: boolean;
  isDirty: boolean;
  isLoading?: boolean;
  displayActions?: boolean; // @todo : It will be mandatory (more convenience for the spike)
  breadcrumb: ReactNode;
  onLocaleChanged?: (localeCode: LocaleCode) => void;
  onChannelChanged?: (channelCode: ChannelCode) => void;
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
    onLocaleChanged: (localeCode: LocaleCode) => void;
    onChannelChanged: (channelCode: ChannelCode) => void;
  };
}

interface HeaderProps extends StateProps, DispatchProps {}

//TODO remove position sticky and z-index
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
                  <ButtonContainer>
                    {secondaryActions}
                    {primaryAction(saveButtonRef)}
                  </ButtonContainer>
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
                {withChannelSwitcher && structure && events && 0 < structure.channels.length ? (
                  <ChannelSwitcher
                    channelCode={context.channel}
                    channels={structure.channels}
                    locale={context.locale}
                    onChannelChange={events.onChannelChanged}
                  />
                ) : null}
                {withLocaleSwitcher && structure && events && 0 < structure.locales.length ? (
                  <LocaleSelector value={context.locale} values={structure.locales} onChange={events.onLocaleChanged} />
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
            ? (localeCode: LocaleCode) => {
                dispatch(catalogLocaleChanged(localeCode));
              }
            : ownProps.onLocaleChanged,
        onChannelChanged:
          undefined === ownProps.onChannelChanged
            ? (channelCode: ChannelCode) => {
                dispatch(catalogChannelChanged(channelCode));
              }
            : ownProps.onChannelChanged,
      },
    };
  }
)(HeaderView);
export {HeaderView};
