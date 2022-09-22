import React from 'react';
import {Breadcrumb, Button, Helper} from 'akeneo-design-system';
import {PageHeader, PimView, useTranslate} from '@akeneo-pim-community/shared';

const IdentifierGeneratorApp = () => {
  const translate = useTranslate();

  return (
    <div>
      <Helper level="error">
        <p>
          Under Construction: The Akeneo Product Team is hard at work developing new features for you. This feature will
          launch soon, but is currently under development.
        </p>
        <p>
          Please do not attempt to use this feature as it could lead to unexpected behaviors that impact your product
          data.
        </p>
      </Helper>
      <PageHeader>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step href="#">{translate('pim_title.pim_settings_index')}</Breadcrumb.Step>
            <Breadcrumb.Step href="#">{translate('pim_title.akeneo_identifier_generator_index')}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
            viewName="pim-identifier-generator-user-navigation"
          />
        </PageHeader.UserActions>
        <PageHeader.Actions>
          <Button onClick={() => null}>{translate('pim_common.create')}</Button>
        </PageHeader.Actions>
        <PageHeader.Title>{translate('pim_title.akeneo_identifier_generator_index')}</PageHeader.Title>
      </PageHeader>
    </div>
  );
};

export {IdentifierGeneratorApp};
