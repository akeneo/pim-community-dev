import React from 'react';
import { FallbackAction } from '../../../../models/FallbackAction';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';

type Props = {
  action: FallbackAction;
} & ActionLineProps;

const AddToCategoryActionLine: React.FC<Props> = ({
  translate,
  action,
  handleDelete,
}) => {
  return (
    <ActionTemplate
      translate={translate}
      title={translate('pimee_catalog_rule.form.edit.add_to_category')}
      helper={translate('pimee_catalog_rule.form.helper.add_to_category')}
      legend={translate('pimee_catalog_rule.form.legend.add_to_category')}
      handleDelete={handleDelete}>
      {/* Not coded yet */}
      {JSON.stringify(action.json)}
    </ActionTemplate>
  );
};

export { AddToCategoryActionLine };
