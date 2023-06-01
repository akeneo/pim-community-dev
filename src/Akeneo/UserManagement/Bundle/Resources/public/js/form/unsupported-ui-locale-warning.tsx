import React, {FC} from 'react';
import {Helper} from 'akeneo-design-system';
import {useTranslate, userContext} from '@akeneo-pim-community/shared';
import {useUiLocales} from '../hooks/useUiLocales';

const UnsupportedUiLocaleWarning: FC = () =>{
  const translate = useTranslate();
  const userDefaultLocaleCode = userContext.get('user_default_locale');
  const locales = useUiLocales();
  const userLocalFound = locales?.find(locale => locale.code === userDefaultLocaleCode);

  return (
      <>
        {!userLocalFound && (
            <Helper level="warning">{translate('pim_user_management.entity.user.properties.helper', {code: userDefaultLocaleCode})}</Helper>
        )}
      </>
  );
};

export {UnsupportedUiLocaleWarning};
