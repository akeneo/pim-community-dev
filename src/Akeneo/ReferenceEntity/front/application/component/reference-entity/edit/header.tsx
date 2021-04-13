import React, {useRef} from 'react';
import {connect} from 'react-redux';
import styled from 'styled-components';
import {getColor} from 'akeneo-design-system';
import {PimView, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import EditState from 'akeneoreferenceentity/application/component/app/edit-state';
import LocaleSwitcher from 'akeneoreferenceentity/application/component/app/locale-switcher';
import File from 'akeneoreferenceentity/domain/model/file';
import Locale from 'akeneoreferenceentity/domain/model/locale';
import {EditState as State} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import {catalogLocaleChanged, catalogChannelChanged} from 'akeneoreferenceentity/domain/event/user';
import Channel from 'akeneoreferenceentity/domain/model/channel';
import ChannelSwitcher from 'akeneoreferenceentity/application/component/app/channel-switcher';
import {getLocales} from 'akeneoreferenceentity/application/reducer/structure';
import {getImageShowUrl} from 'akeneoreferenceentity/tools/media-url-generator';

const Image = styled.img`
  width: 142px;
  height: 142px;
  border: 1px solid ${getColor('grey', 80)};
  margin-right: 20px;
  border-radius: 4px;
  transition: filter 0.3s;
  object-fit: contain;
`;

interface OwnProps {
  label: string;
  image: File;
  primaryAction?: (defaultFocus: React.RefObject<any>) => JSX.Element | null;
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
  structure: {
    locales: Locale[];
    channels: Channel[];
  };
}

interface DispatchProps {
  events: {
    onLocaleChanged: (locale: Locale) => void;
    onChannelChanged: (channel: Channel) => void;
  };
}

interface HeaderProps extends StateProps, DispatchProps {}

const Header = ({
  label,
  image,
  primaryAction,
  secondaryActions,
  withChannelSwitcher,
  withLocaleSwitcher,
  isDirty,
  isLoading,
  displayActions,
  breadcrumb,
  context,
  structure,
  events,
}: HeaderProps) => {
  const translate = useTranslate();
  const defaultFocus = useRef<any>(null);

  return (
    <header className="AknTitleContainer">
      <div className="AknTitleContainer-line">
        <Image
          alt={translate('pim_reference_entity.reference_entity.img', {label})}
          src={getImageShowUrl(image, 'thumbnail')}
        />
        <div className="AknTitleContainer-mainContainer">
          <div>
            <div className="AknTitleContainer-line">
              <div className="AknTitleContainer-breadcrumbs">{breadcrumb}</div>
              <div className="AknTitleContainer-buttonsContainer">
                <div className={`AknLoadingIndicator ${true === isLoading ? '' : 'AknLoadingIndicator--hidden'}`} />
                <div className="AknTitleContainer-userMenuContainer user-menu">
                  <PimView
                    className={`AknTitleContainer-userMenu ${displayActions ? '' : 'AknTitleContainer--withoutMargin'}`}
                    viewName="pim-reference-entity-index-user-navigation"
                  />
                </div>
                <div className="AknTitleContainer-actionsContainer AknButtonList">
                  {secondaryActions?.()}
                  <div className="AknTitleContainer-rightButton">{primaryAction?.(defaultFocus)}</div>
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
                {withChannelSwitcher ? (
                  <ChannelSwitcher
                    channelCode={context.channel}
                    channels={structure.channels}
                    locale={context.locale}
                    onChannelChange={events.onChannelChanged}
                    className="AknDropdown--right"
                  />
                ) : null}
                {withLocaleSwitcher ? (
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
)(Header);
