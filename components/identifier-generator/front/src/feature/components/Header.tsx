import React from 'react';
import {PageHeader, PimView, UnsavedChanges, useTranslate} from '@akeneo-pim-community/shared';
import {Breadcrumb} from 'akeneo-design-system';
import {useIdentifierGeneratorContext} from '../context';

type HeaderProps = {
  children: React.ReactNode;
};

const Header: React.FC<HeaderProps> = ({children}) => {
  const translate = useTranslate();
  const identifierGeneratorContext = useIdentifierGeneratorContext();

  return (
    <>
      <PageHeader>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            {/*TODO Add alert when going out this page if not saved*/}
            <Breadcrumb.Step href="#">{translate('pim_title.pim_settings_index')}</Breadcrumb.Step>
            <Breadcrumb.Step href="/settings/identifier-generator">
              {translate('pim_title.akeneo_identifier_generator_index')}
            </Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
            viewName="pim-identifier-generator-user-navigation"
          />
        </PageHeader.UserActions>
        <PageHeader.Actions>{children}</PageHeader.Actions>
        <PageHeader.Title>{translate('pim_title.akeneo_identifier_generator_index')}</PageHeader.Title>
        <PageHeader.State>
          {identifierGeneratorContext.unsavedChanges.hasUnsavedChanges && <UnsavedChanges />}
        </PageHeader.State>
      </PageHeader>
    </>
  );
};

export {Header};
