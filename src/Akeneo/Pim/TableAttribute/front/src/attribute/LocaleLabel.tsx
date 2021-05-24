import React from 'react';
import {LoaderIcon, Locale as LocaleWithFlag} from 'akeneo-design-system';
import { Locale, LocaleCode, useIsMounted, useRouter } from '@akeneo-pim-community/shared';
import { fetchLocale } from "../locale/LocaleFetcher";

type LocaleProps = {
  localeCode: LocaleCode;
};

const LocaleLabel: React.FC<LocaleProps> = ({localeCode}) => {
  const [locale, setLocale] = React.useState<Locale>();
  const isMounted = useIsMounted();
  const router = useRouter();

  React.useEffect(() => {
    fetchLocale(router, localeCode).then((locale) => {
      if (isMounted()) {
        setLocale(locale);
      }
    });
  }, []);

  if (!locale) {
    return <LoaderIcon />;
  }

  return <LocaleWithFlag code={locale.code} languageLabel={locale.language} />;
};

export {LocaleLabel};
