import * as React from 'react';
import Locale from 'akeneoenrichedentity/domain/model/locale';

const Flag = ({locale, displayLanguage}: {locale: Locale; displayLanguage: boolean}) => {
  if (!locale) {
    return null;
  }

  const region = locale.code.split('_')[1];
  const iconClass = `flag flag-${region.toLowerCase()}`;

  return (
    <span>
      <i className={iconClass} />
      &nbsp;{displayLanguage ? <span className="language">{locale.language}</span> : ''}
    </span>
  );
};

export default Flag;
