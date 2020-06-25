import React from 'react';
import { ClearGroupsAction } from '../../../../models/actions';
import { ActionLineProps } from './ActionLineProps';
import { useTranslate } from '../../../../dependenciesTools/hooks';
import { ActionTemplate } from './ActionTemplate';
import { useRegisterConst } from '../../hooks/useRegisterConst';

type Props = {
  action: ClearGroupsAction;
} & ActionLineProps;

const ClearGroupsActionLine: React.FC<Props> = ({
  lineNumber,
  action,
  handleDelete,
}) => {
  const translate = useTranslate();
  useRegisterConst(`content.actions[${lineNumber}]`, action);

  return (
    <ActionTemplate
      title={translate(
        'pimee_catalog_rule.form.edit.actions.clear_groups.title'
      )}
      helper={translate('pimee_catalog_rule.form.helper.clear_groups')}
      legend={translate('pimee_catalog_rule.form.helper.clear_groups')}
      handleDelete={handleDelete}
    />
  );
};

export { ClearGroupsActionLine };
