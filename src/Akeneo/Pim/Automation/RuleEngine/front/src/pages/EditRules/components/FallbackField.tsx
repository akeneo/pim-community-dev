import React, { ReactElement } from 'react';
import { Flag } from '../../../components/Flag';

type Props = {
  field: string;
  scope: string | null;
  locale: string | null;
};

const FallbackField: React.FC<Props> = ({ field, scope, locale }) => {
  const displayLocale = (locale: string | null): ReactElement | null => {
    if (null === locale) {
      return null;
    }

    const parts = locale.split(/_/);
    const countryCode = parts.length === 3 ? parts[2]: parts[1];

    return (
      <>
        <Flag locale={locale} flagDescription={countryCode} /> {parts[0]}
      </>
    );
  };

  return (
    <>
      <span className='AknRule-attribute'>{field}</span>
      {(scope || locale) && (
        <span className='AknRule-attribute'>
          {' [ '}
          {displayLocale(locale)}
          {scope && locale && ' | '}
          {scope}
          {' ] '}
        </span>
      )}
    </>
  );
};

export { FallbackField };
