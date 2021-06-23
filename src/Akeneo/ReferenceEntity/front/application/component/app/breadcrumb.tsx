import React from 'react';
import {Breadcrumb} from 'akeneo-design-system';
import {useRoute, useRouter, useTranslate} from '@akeneo-pim-community/shared';

type RefEntityBreadcrumbProps = {
  referenceEntityIdentifier: string;
  recordCode?: string;
};

const RefEntityBreadcrumb = ({referenceEntityIdentifier, recordCode}: RefEntityBreadcrumbProps) => {
  const translate = useTranslate();
  const indexHref = `#${useRoute('akeneo_reference_entities_reference_entity_index')}`;
  const referenceEntityHref = `#${useRoute('akeneo_reference_entities_reference_entity_edit', {
    identifier: referenceEntityIdentifier,
    tab: 'record',
  })}`;

  const router = useRouter();

  const children = [
    <Breadcrumb.Step onClick={() => router.redirect(indexHref)}>
      {translate('pim_reference_entity.reference_entity.breadcrumb')}
    </Breadcrumb.Step>,
    <Breadcrumb.Step onClick={() => router.redirect(referenceEntityHref)}>{referenceEntityIdentifier}</Breadcrumb.Step>,
  ];

  if (undefined !== recordCode) {
    children.push(<Breadcrumb.Step>{recordCode}</Breadcrumb.Step>);
  }

  return <Breadcrumb children={children} />;
};

export {RefEntityBreadcrumb};
