import React from 'react';
import {Breadcrumb, Button, Helper} from 'akeneo-design-system';
import {PageHeader, PimView, useTranslate} from "@akeneo-pim-community/shared";

const IdentifierGeneratorApp = () => {
  const translate = useTranslate();

  return (
    <div>
      <Helper level="error">
        This feature is currently under development. Using it can lead to unexpected behaviors.
      </Helper>
      <PageHeader>
        <PageHeader.Breadcrumb>
        <Breadcrumb>
          <Breadcrumb.Step href="#">
            {translate('pim_title.pim_settings_index')}
          </Breadcrumb.Step>
          <Breadcrumb.Step href="#">
            {translate('pim_title.akeneo_identifier_generator_index')}
          </Breadcrumb.Step>
          <Breadcrumb.Step>
            third
          </Breadcrumb.Step>
        </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
            viewName="pim-measurements-user-navigation"
          />
        </PageHeader.UserActions>
        <PageHeader.Actions>
            <Button onClick={() => null}>{translate('pim_common.create')}</Button>
        </PageHeader.Actions>
        <PageHeader.Title>
          {translate('pim_title.akeneo_identifier_generator_index')}
        </PageHeader.Title>
      </PageHeader>
    </div>
  )
};

export {IdentifierGeneratorApp};
