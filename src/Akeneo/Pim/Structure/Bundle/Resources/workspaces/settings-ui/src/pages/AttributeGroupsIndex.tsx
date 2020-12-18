import React, {FC, useEffect} from 'react';
import {PimView, useRoute, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {PageContent, PageHeader} from '@akeneo-pim-community/shared';
import {AttributeGroupsCreateButton, AttributeGroupsDataGrid} from '../components';
import {useAttributeGroupsIndexState} from '../hooks';
import {Breadcrumb} from 'akeneo-design-system';

const AttributeGroupsIndex: FC = () => {
  const {groups, load, isPending} = useAttributeGroupsIndexState();
  const translate = useTranslate();
  const settingsHomePageRoute = useRoute('pim_enrich_attribute_index');

  useEffect(() => {
    (async () => {
      await load();
    })();
  }, []);

  return (
    <>
      <PageHeader showPlaceholder={isPending}>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step href={settingsHomePageRoute}>{translate('pim_menu.tab.settings')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_enrich.entity.attribute_group.plural_label')}</Breadcrumb.Step>
          </Breadcrumb>
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
