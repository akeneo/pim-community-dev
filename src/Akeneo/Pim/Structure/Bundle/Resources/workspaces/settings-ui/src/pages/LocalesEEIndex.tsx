import React, {FC, useEffect, useState} from 'react';
import {PageContent, PageHeader} from '@akeneo-pim-community/shared';
import {PimView, useRoute, useSecurity, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {LocalesDataGrid, useLocalesIndexState} from '@akeneo-pim-community/settings-ui';
import styled from 'styled-components';
import {Breadcrumb, getColor, Helper as BaseHelper} from 'akeneo-design-system';
import {followEditLocale} from '../user-actions';

const Helper = styled(BaseHelper)`
  margin-bottom: 20px;
`;

const HelperContent = styled.span`
  a {
    color: ${getColor('brand', 100)};

    &:hover {
      color: ${getColor('brand', 120)};
    }
  }
`;

const LocalesEEIndex: FC = () => {
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const {locales, load, isPending} = useLocalesIndexState();
  const settingsHomePageRoute = useRoute('pim_enrich_attribute_index');
  const settingsChannelPageRoute = useRoute('pim_enrich_channel_index');

  useEffect(() => {
    (async () => {
      await load();
    })();
  }, []);

  const [localeCount, setLocaleCount] = useState<number>(locales.length);

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
          {translate('pim_enrich.entity.locale.page_title.index', {count: localeCount.toString()}, localeCount)}
        </PageHeader.Title>
      </PageHeader>
      <PageContent>
        <Helper level="info">
          <HelperContent
            dangerouslySetInnerHTML={{
              __html: translate('pim_enrich.entity.locale.helper', {
                href: `#${settingsChannelPageRoute}`,
              }),
            }}
          />
        </Helper>
        <LocalesDataGrid
          locales={locales}
          followLocale={isGranted('pimee_enrich_locale_edit_permissions') ? followEditLocale : undefined}
          onLocaleCountChange={setLocaleCount}
        />
      </PageContent>
    </>
  );
};

export {LocalesEEIndex};
