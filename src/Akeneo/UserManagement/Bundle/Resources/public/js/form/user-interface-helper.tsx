import React, {FC, useState} from 'react';
import {Helper} from 'akeneo-design-system';
import {useTranslate} from "@akeneo-pim-community/shared";
import {userContext} from '@akeneo-pim-community/shared/lib/dependencies/user-context';

type InterfaceNormalizedCategory = {
  code: string;
  labels: {[key: string]: string};
};
const UserInterfaceHelper: FC = () =>{
  const translate = useTranslate();
  const [userLocalIsPresent, setUserLocalIsPresent] = useState<boolean>(true);
  const FetcherRegistry = require('pim/fetcher-registry');
  const userDefaultLocaleCode = userContext.get('user_default_locale');
  FetcherRegistry.getFetcher('ui-locale')
      .fetchAll()
      .then((locales: InterfaceNormalizedCategory[]) => {
        const userLocalFound = locales.find(locale => locale.code === userDefaultLocaleCode);
        setUserLocalIsPresent(userLocalFound !== undefined);
      });

  return (
      <>
        {!userLocalIsPresent && (
            <Helper level="warning">{translate('pim_user_management.entity.user.properties.helper', {code: userDefaultLocaleCode})}</Helper>
        )}
      </>
  );
};

export {UserInterfaceHelper};
