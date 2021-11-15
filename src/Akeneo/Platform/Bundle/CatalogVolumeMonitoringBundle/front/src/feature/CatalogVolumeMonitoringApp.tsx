import React from 'react';
import {PageContent, PageHeader, PimView, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {Breadcrumb} from 'akeneo-design-system';

const CatalogVolumeMonitoringApp = () => {
  const translate = useTranslate();
  const systemHref = useRoute('pim_system_index');

  return (
    <>
      <PageHeader>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step href={`#${systemHref}`}>{translate('pim_menu.tab.system')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_menu.item.catalog_volume')}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
        </PageHeader.UserActions>
        <PageHeader.Title>{translate('pim_menu.item.catalog_volume')}</PageHeader.Title>
      </PageHeader>
      <PageContent>
        <div>Work in progess...</div>
      </PageContent>
    </>
  );
};

export {CatalogVolumeMonitoringApp};
