import React, {FC} from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Breadcrumb, BreadcrumbItem} from '@akeneo-pim-community/shared';

const AttributeGroupsBreadcrumb: FC = () => {
  const translate = useTranslate();
  return (
    <Breadcrumb>
      <BreadcrumbItem>{translate('pim_menu.tab.settings')}</BreadcrumbItem>
      <BreadcrumbItem>{translate('pim_enrich.entity.attribute_group.plural_label')}</BreadcrumbItem>
    </Breadcrumb>
  );
};

export {AttributeGroupsBreadcrumb};
