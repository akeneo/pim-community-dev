import React from 'react';
import {LoaderIcon, Locale as LocaleWithFlag} from 'akeneo-design-system';
import {Locale, LocaleCode, useIsMounted} from '@akeneo-pim-community/shared';
const FetcherRegistry = require('pim/fetcher-registry');

type LocaleProps = {
  localeCode: LocaleCode;
};

const LocaleLabel: React.FC<LocaleProps> = ({localeCode}) => {
  const [locale, setLocale] = React.useState<Locale>();
  const isMounted = useIsMounted();

  React.useEffect(() => {
    FetcherRegistry.initialize().then(() => {
      FetcherRegistry.getFetcher('locale')
        .fetch(localeCode)
        .then((locale: Locale) => {
          if (isMounted()) {
            setLocale(locale);
          }
        });
    });
  }, []);

  if (!locale) {
    return <LoaderIcon />;
  }

  return <LocaleWithFlag code={locale.code} languageLabel={locale.language} />;
};

export {LocaleLabel};
