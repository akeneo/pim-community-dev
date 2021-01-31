import React, {FC, useEffect} from 'react';
import {PageContent, PageHeader} from '@akeneo-pim-community/shared';
import {PimView, useRoute, useSecurity, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {useLocalesIndexState} from '@akeneo-pim-community/settings-ui';
import styled from 'styled-components';
import {Breadcrumb, getColor, Helper as BaseHelper} from 'akeneo-design-system';
import {followEditLocale} from '../user-actions';
import {LocalesEEDataGrid} from '../components';
import {LocaleToolbar} from '../components/toolbars';
import {LocalesGridDictionariesProvider} from '../components/datagrids/LocalesGridDictionariesProvider';
import {LocaleSelectionProvider} from '../components/datagrids/LocaleSelectionProvider';
import {useLocalesDictionaryInfo} from '../hooks';

const Helper = styled(BaseHelper)`
  margin: 0px 40px 20px 40px;
  padding-right: 0;
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
  const {refresh, getDictionaryTotalWords, localesDictionaryInfo} = useLocalesDictionaryInfo(locales);

  useEffect(() => {
    (async () => {
      await load();
    })();
  }, []);

  return (
    <LocaleSelectionProvider locales={locales}>
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
      <PageContent style={{display: 'flex', flexDirection: 'column', padding: '0'}}>
        <div style={{flexGrow: 1, overflowY: 'auto'}}>
          <Helper level="info">
            <HelperContent
              dangerouslySetInnerHTML={{
                __html: translate('pim_enrich.entity.locale.helper', {
                  href: `#${settingsChannelPageRoute}`,
                }),
              }}
            />
          </Helper>
          <LocalesEEDataGrid
            locales={locales}
            followLocale={isGranted('pimee_enrich_locale_edit_permissions') ? followEditLocale : undefined}
            getDictionaryTotalWords={getDictionaryTotalWords}
          />
        </div>
        <LocalesGridDictionariesProvider refreshDictionaryInfo={refresh} localesDictionaryInfo={localesDictionaryInfo}>
          <LocaleToolbar />
        </LocalesGridDictionariesProvider>
      </PageContent>
    </LocaleSelectionProvider>
  );
};

export {LocalesEEIndex};
