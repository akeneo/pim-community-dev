import * as React from 'react';
import Locale from 'pimfront/app/domain/model/locale';
import Flag from 'pimfront/app/application/component/flag';
const __ = require('oro/translator');

export default class LocaleSwitcher extends React.Component<
  {locale: string, locales: Locale[], onLocaleChange: (locale: Locale) => void},
  {open: boolean, locale: string}
> {
  constructor (props: any) {
    super(props);

    this.state = {
      open: false,
      locale: props.locale
    };
  }

  open () {
    this.setState({open: true});
  }

  switchLocale (locale: Locale) {
    if (locale.code !== this.state.locale) {
      this.setState({locale: locale.code});
      this.props.onLocaleChange(locale);
    }

    this.close();
  }

  close () {
    this.setState({open: false});
  }

  render () {
    const openClass = this.state.open ? 'AknDropdown-menu--open' : '';
    const selectedLocale: Locale|undefined = this.props.locales.find((locale: Locale) => locale.code === this.state.locale);
    if (undefined === selectedLocale) {
      return null;
    }

    const locales = this.props.locales.map((locale: Locale) => {
      const menuLinkClass = `AknDropdown-menuLink ${locale.code === selectedLocale.code
          ? `AknDropdown-menuLink--active`: ''}`;

      return (
        <div key={locale.code} className={menuLinkClass} data-locale="en_US" onClick={() => this.switchLocale(locale)}>
          <span className="label">
            <Flag locale={locale} displayLanguage/>
          </span>
        </div>
      );
    });

    return (
      <div className="AknDropdown">
        <div onClick={this.open.bind(this)}>
          <div className="AknColumn-subtitle">{__('Locale')}</div>
          <div className="AknColumn-value value">
            <Flag locale={selectedLocale} displayLanguage/>
          </div>
        </div>
        <div className={'AknDropdown-menu ' + openClass}>
          <div className="AknDropdown-menuTitle">{__('Locale')}</div>
          {locales}
        </div>
      </div>
    );
  }
}
