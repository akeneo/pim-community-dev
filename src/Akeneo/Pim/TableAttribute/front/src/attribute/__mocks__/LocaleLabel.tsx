import React from 'react';
import {LocaleCode} from '@akeneo-pim-community/shared';
import {Locale as LocaleWithFlag} from 'akeneo-design-system';

type LocaleProps = {
  localeCode: LocaleCode;
};

const LocaleLabel: React.FC<LocaleProps> = ({localeCode}) => {
  if (localeCode === 'en_US') {
    return <LocaleWithFlag code={'en_US'} languageLabel={'English'} />;
  }
  if (localeCode === 'fr_FR') {
    return <LocaleWithFlag code={'fr_FR'} languageLabel={'French'} />;
  }
  return <>{localeCode}</>;
};

export {LocaleLabel};
