import React, {FC} from 'react';
import {Helper, Link} from 'akeneo-design-system';
import {useTranslate, userContext} from '@akeneo-pim-community/shared';
import {useUiLocales} from '../hooks/useUiLocales';

const UnsupportedUiLocaleWarning: FC = () => {
  const translate = useTranslate();
  const userDefaultLocaleCode = userContext.get('user_default_locale');
  const locales = useUiLocales();
  const userLocalFound = locales?.find(locale => locale.code === userDefaultLocaleCode);

  return (
    <>
      {!userLocalFound && (
        <Helper level="warning">
          <span>
            {translate('pim_user_management.entity.user.properties.not_fully_supported_locale', {
              code: userDefaultLocaleCode,
            })}
          </span>
          &nbsp;
          <Link href="https://crowdin.com/project/akeneo" target="_blank">
            Crowdin
          </Link>
        </Helper>
      )}
    </>
  );
};

export {UnsupportedUiLocaleWarning};
