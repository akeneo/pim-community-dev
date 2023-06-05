import React, {FC} from 'react';
import {Helper, Link} from 'akeneo-design-system';
import {useTranslate, userContext} from '@akeneo-pim-community/shared';
import {useUiLocales} from '../hooks/useUiLocales';
import styled from 'styled-components';

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
            {translate('pim_user_management.entity.user.properties.not_fully_supported_locale_start', {
              code: userDefaultLocaleCode,
            })}
            &nbsp;
          </span>
          <Link href="https://crowdin.com/project/akeneo" target="_blank">
            Crowdin
          </Link>
          <span>
            &nbsp;
            {translate('pim_user_management.entity.user.properties.not_fully_supported_locale_end', {
              code: userDefaultLocaleCode,
            })}
          </span>
        </Helper>
      )}
    </>
  );
};

export {UnsupportedUiLocaleWarning};
