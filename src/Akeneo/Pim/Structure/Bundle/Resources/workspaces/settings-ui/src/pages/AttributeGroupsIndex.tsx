import React, {FC, useEffect} from 'react';
import {PimView, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {PageContent, PageHeader} from '@akeneo-pim-community/shared';
import {AttributeGroupsBreadcrumb, AttributeGroupsCreateButton, AttributeGroupsDataGrid} from '../components';
import {useAttributeGroupsIndexState} from '../hooks';

const AttributeGroupsIndex: FC = () => {
  const {groups, load, isPending} = useAttributeGroupsIndexState();
  const translate = useTranslate();

  useEffect(() => {
    (async () => {
      await load();
    })();
  }, []);

  return (
    <>
      <PageHeader showPlaceholder={isPending}>
        <PageHeader.Breadcrumb>
          <AttributeGroupsBreadcrumb />
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
        </PageHeader.UserActions>
        <PageHeader.Actions>
          <AttributeGroupsCreateButton />
        </PageHeader.Actions>
        <PageHeader.Title>
          {translate(
            'pim_enrich.entity.attribute_group.result_count',
            {count: groups.length.toString()},
            groups.length
          )}
        </PageHeader.Title>
      </PageHeader>
      <PageContent>
        <AttributeGroupsDataGrid groups={groups} />
      </PageContent>
    </>
  );
};

export {AttributeGroupsIndex};
