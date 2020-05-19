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

    return (
      <>
        <Flag locale={locale} flagDescription={locale} /> {locale}
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
