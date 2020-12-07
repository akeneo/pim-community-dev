import React, {FC, useEffect} from 'react';
import {PageContent, PageHeader} from '@akeneo-pim-community/shared';
import {PimView, useRoute, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {LocalesDataGrid} from '../components';
import {useLocalesIndexState} from '../hooks';
import {Breadcrumb} from 'akeneo-design-system';

const LocalesIndex: FC = () => {
  const translate = useTranslate();
  const {locales, load, isPending} = useLocalesIndexState();
  const settingsHomePageRoute = useRoute('pim_enrich_attribute_index');

  useEffect(() => {
    (async () => {
      await load();
    })();
  }, []);

  return (
    <>
      <PageHeader showPlaceholder={isPending}>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step href={`#${settingsHomePageRoute}`}>{translate('pim_menu.tab.settings')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_enrich.entity.locale.plural_label')}</Breadcrumb.Step>
          </Breadcrumb>
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
