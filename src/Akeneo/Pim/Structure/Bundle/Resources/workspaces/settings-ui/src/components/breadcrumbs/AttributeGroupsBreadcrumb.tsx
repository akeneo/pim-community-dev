import React, {FC} from 'react';
import {useRoute, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Breadcrumb} from 'akeneo-design-system';

const AttributeGroupsBreadcrumb: FC = () => {
  const translate = useTranslate();
  const settingsHref = `#${useRoute('pim_enrich_attribute_index')}`;

  return (
    <Breadcrumb>
      <Breadcrumb.Step href={settingsHref}>{translate('pim_menu.tab.settings')}</Breadcrumb.Step>
      <Breadcrumb.Step>{translate('pim_enrich.entity.attribute_group.plural_label')}</Breadcrumb.Step>
    </Breadcrumb>
  );
};

export {AttributeGroupsBreadcrumb};
