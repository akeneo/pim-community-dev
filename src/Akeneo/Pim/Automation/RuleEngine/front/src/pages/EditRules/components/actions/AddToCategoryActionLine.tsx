import React from 'react';
import { FallbackAction } from '../../../../models/actions/FallbackAction';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { useTranslate } from '../../../../dependenciesTools/hooks';

type Props = {
  action: FallbackAction;
} & ActionLineProps;

const AddToCategoryActionLine: React.FC<Props> = ({ action, handleDelete }) => {
  const translate = useTranslate();

  return (
    <ActionTemplate
      title={translate('pimee_catalog_rule.form.edit.add_to_category')}
      helper={translate('pimee_catalog_rule.form.helper.add_to_category')}
      legend={translate('pimee_catalog_rule.form.legend.add_to_category')}
      handleDelete={handleDelete}>
      {/* Not coded yet */}
      {JSON.stringify(action)}
    </ActionTemplate>
  );
};

export { AddToCategoryActionLine };
