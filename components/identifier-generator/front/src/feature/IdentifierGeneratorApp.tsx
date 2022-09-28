import React from 'react';
import {Breadcrumb, Button, Helper} from 'akeneo-design-system';
import {PageHeader, PimView, useTranslate} from '@akeneo-pim-community/shared';
import {IdentifierAttributeSelector} from './components';
import {QueryClient, QueryClientProvider} from 'react-query';

// Create a client
const queryClient = new QueryClient();

const IdentifierGeneratorApp = () => {
  const translate = useTranslate();

  return (
    <QueryClientProvider client={queryClient}>
      <div>
        <Helper level="error">
          Under Construction: The Akeneo Product Team is hard at work developing new features for you. This feature will
          launch soon, but is currently under development. Please do not attempt to use this feature as it could lead to
          unexpected behaviors that impact your product data.
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
        <IdentifierAttributeSelector code="sku" />
      </div>
    </QueryClientProvider>
  );
};

export {IdentifierGeneratorApp};
