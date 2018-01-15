import * as React from 'react';

export default ({locale, displayLanguage}: {locale: string, displayLanguage: boolean}) => {
  if (!locale) {
    return null;
  }
  const [language, country] = locale.split('_');
  const iconClass = `flag flag-${country.toLowerCase()}`;

  return (
    <span>
      <i className={iconClass}></i> {displayLanguage ? <span className="language">{language}</span> : ''}
    </span>
  );
};
