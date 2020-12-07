import React, {FC, useEffect} from 'react';
import {PageContent, PageHeader} from '@akeneo-pim-community/shared';
import {PimView, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {LocalesBreadcrumb, LocalesDataGrid} from '../components';
import {useLocalesIndexState} from '../hooks';

const LocalesIndex: FC = () => {
  const translate = useTranslate();
  const {locales, load, isPending} = useLocalesIndexState();

  useEffect(() => {
    (async () => {
      await load();
    })();
  }, []);

  return (
    <>
      <PageHeader showPlaceholder={isPending}>
        <PageHeader.Breadcrumb>
          <LocalesBreadcrumb />
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
        </PageHeader.UserActions>
        <PageHeader.Title>
          {translate('pim_enrich.entity.locale.page_title.index', {count: locales.length.toString()}, locales.length)}
        </PageHeader.Title>
      </PageHeader>
      <PageContent>
        <LocalesDataGrid locales={locales} />
      </PageContent>
    </>
  );
};

export {LocalesIndex};
