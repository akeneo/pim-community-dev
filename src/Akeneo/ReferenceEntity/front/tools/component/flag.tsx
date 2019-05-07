import * as React from 'react';
import Locale from 'akeneoreferenceentity/domain/model/locale';

const Flag = ({
  locale,
  displayLanguage,
  className = '',
}: {
  locale: Locale;
  displayLanguage: boolean;
  className?: string;
}) => {
  if (!locale) {
    return null;
  }

  const region = locale.code.split('_')[locale.code.split('_').length - 1];
  const iconClass = `flag flag-${region.toLowerCase()}`;

  return (
    <span className={className}>
      <i className={iconClass} />
      &nbsp;
      {displayLanguage ? <span className="language">{locale.language}</span> : ''}
    </span>
  );
};

export default Flag;
