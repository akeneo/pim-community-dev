import React from 'react';
import {PageHeader} from '@akeneo-pim-community/shared';
import {Breadcrumb} from 'akeneo-design-system';
import {PimView, useTranslate, useUserContext} from '@akeneo-pim-community/legacy-bridge';
import {generateRandomNumber} from '../helpers';

const MediaUrlGenerator = require('pim/media-url-generator');

const Header = () => {
  const translate = useTranslate();
  const userContext = useUserContext();

  const getRandomWelcomeSentence = (): string => {
    const welcomeSentences: string[] = ['pim_dashboard.welcome_sentence.message1'];

    return welcomeSentences[generateRandomNumber(welcomeSentences.length - 1)];
  };

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
      <PageHeader.Title>
        {translate('pim_dashboard.greetings', {
          // @ts-ignore
          name: userContext.get('first_name').charAt(0).toUpperCase() + userContext.get('first_name').slice(1),
        })}
        &nbsp;
        {translate(getRandomWelcomeSentence())}
      </PageHeader.Title>
    </PageHeader>
  );
};

export {Header};
