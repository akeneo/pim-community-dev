import React from 'react';
import {PageHeader} from '@akeneo-pim-community/shared';
import {Breadcrumb} from 'akeneo-design-system';
import {PimView, useTranslate, useUserContext} from '@akeneo-pim-community/legacy-bridge';

const MediaUrlGenerator = require('pim/media-url-generator');

const Header = () => {
  const translate = useTranslate();
  const userContext = useUserContext();

  return (
    <PageHeader>
      <PageHeader.Illustration
        // @ts-ignore
        src={MediaUrlGenerator.getMediaShowUrl(userContext.get('avatar').filePath, 'thumbnail_small')}
      />
      <PageHeader.Breadcrumb>
        <Breadcrumb>
          <Breadcrumb.Step>{translate('pim_menu.tab.activity')}</Breadcrumb.Step>
          <Breadcrumb.Step>{translate('pim_dashboard.title')}</Breadcrumb.Step>
        </Breadcrumb>
      </PageHeader.Breadcrumb>
      <PageHeader.UserActions>
        <PimView
          viewName="pim-menu-user-navigation"
          className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
        />
      </PageHeader.UserActions>
      <PageHeader.Title>{translate('pim_dashboard.title')}</PageHeader.Title>
    </PageHeader>
  );
};

export {Header};
