import React, {FC, useEffect, useState} from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {PageContent, PageHeader} from '@akeneo-pim-community/shared';
import {
  AttributeGroupsBreadcrumb,
  AttributeGroupsCreateButton,
  AttributeGroupsDataGrid,
  AttributeGroupsUserButtons,
} from '../components';
import {useAttributeGroupsDataGridState, useMountedRef} from '../hooks';

const AttributeGroupsIndex: FC = () => {
  const [showPlaceholder, setShowPlaceholder] = useState(true);
  const mounted = useMountedRef();
  const {groups, load} = useAttributeGroupsDataGridState();
  const translate = useTranslate();

  useEffect(() => {
    (async () => {
      await load();
      if (mounted.current) {
        setShowPlaceholder(false);
      }
    })();
  }, []);

  return (
    <>
      <PageHeader showPlaceholder={showPlaceholder}>
        <PageHeader.Breadcrumb>
          <AttributeGroupsBreadcrumb />
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <AttributeGroupsUserButtons />
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
