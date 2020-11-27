import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';
import EditState from 'akeneoreferenceentity/application/component/app/edit-state';
import Image from 'akeneoreferenceentity/application/component/app/image';
import {connect} from 'react-redux';
import LocaleSwitcher from 'akeneoreferenceentity/application/component/app/locale-switcher';
import PimView from 'akeneoreferenceentity/infrastructure/component/pim-view';
import File from 'akeneoreferenceentity/domain/model/file';
import Locale from 'akeneoreferenceentity/domain/model/locale';
import {EditState as State} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import {catalogLocaleChanged, catalogChannelChanged} from 'akeneoreferenceentity/domain/event/user';
import Channel from 'akeneoreferenceentity/domain/model/channel';
import ChannelSwitcher from 'akeneoreferenceentity/application/component/app/channel-switcher';
import {getLocales} from 'akeneoreferenceentity/application/reducer/structure';

interface OwnProps {
  label: string;
  image: File;
  primaryAction: (defaultFocus: React.RefObject<any>) => JSX.Element | null;
  secondaryActions: () => JSX.Element | null;
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

class Header extends React.Component<HeaderProps> {
  private defaultFocus: React.RefObject<any>;
  static defaultProps = {
    displayActions: true,
  };

  constructor(props: HeaderProps) {
    super(props);

    this.defaultFocus = React.createRef();
  }

  componentDidMount() {
    if (null !== this.defaultFocus.current) {
      this.defaultFocus.current.focus();
    }
  }

  render() {
    const {
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
    } = this.props;

    return (
      <header className="AknTitleContainer">
        <div className="AknTitleContainer-line">
          <Image alt={__('pim_reference_entity.reference_entity.img', {'{{ label }}': label})} image={image} />
          <div className="AknTitleContainer-mainContainer">
            <div>
              <div className="AknTitleContainer-line">
                <div className="AknTitleContainer-breadcrumbs">{breadcrumb}</div>
                <div className="AknTitleContainer-buttonsContainer">
                  <div className={`AknLoadingIndicator ${true === isLoading ? '' : 'AknLoadingIndicator--hidden'}`} />
                  <div className="AknTitleContainer-userMenuContainer user-menu">
                    <PimView
                      className={`AknTitleContainer-userMenu ${
                        displayActions ? '' : 'AknTitleContainer--withoutMargin'
                      }`}
                      viewName="pim-reference-entity-index-user-navigation"
                    />
                  </div>
                  <div className="AknTitleContainer-actionsContainer AknButtonList">
                    {secondaryActions()}
                    <div className="AknTitleContainer-rightButton">{primaryAction(this.defaultFocus)}</div>
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
  }
}

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
