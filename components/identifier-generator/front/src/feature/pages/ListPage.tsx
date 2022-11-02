import React from 'react';
import {Common} from '../components';
import {PageContent, PageHeader, PimView, useTranslate} from '@akeneo-pim-community/shared';
import {Breadcrumb, Button} from 'akeneo-design-system';

type ListPageProps = {
  onCreate: () => void;
  isCreateEnabled: boolean;
};

const ListPage: React.FC<ListPageProps> = ({onCreate, isCreateEnabled}) => {
  const translate = useTranslate();

  return (
    <>
      <Common.Helper />
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
          <Button onClick={onCreate} disabled={!isCreateEnabled}>
            {translate('pim_common.create')}
          </Button>
        </PageHeader.Actions>
        <PageHeader.Title>{translate('pim_title.akeneo_identifier_generator_index')}</PageHeader.Title>
      </PageHeader>
      <PageContent />
    </>
  );
};

export {ListPage};
