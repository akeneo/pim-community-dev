import * as React from 'react';
import Locale from 'pimfront/app/domain/model/locale';

export default ({locale, displayLanguage}: {locale: Locale, displayLanguage: boolean}) => {
  if (!locale) {
    return null;
  }
  const [language, country] = locale.code.split('_');
  const iconClass = `flag flag-${country.toLowerCase()}`;

  return (
    <span>
      <i className={iconClass}></i>
      {displayLanguage ? <span className="language">{locale.language ? locale.language : language}</span> : ''}
    </span>
  );
};
