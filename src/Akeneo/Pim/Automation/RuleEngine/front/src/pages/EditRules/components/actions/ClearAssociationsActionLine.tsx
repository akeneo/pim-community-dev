import React from 'react';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { useRegisterConst } from '../../hooks/useRegisterConst';
import { useTranslate } from '../../../../dependenciesTools/hooks';
import { ClearAssociationsAction } from '../../../../models/actions/ClearAssociationsAction';

type Props = {
  action: ClearAssociationsAction;
} & ActionLineProps;

const ClearAssociationsActionLine: React.FC<Props> = ({
  lineNumber,
  action,
  handleDelete,
}) => {
  const translate = useTranslate();
  useRegisterConst(`content.actions[${lineNumber}]`, action);

  return (
    <ActionTemplate
      title={translate(
        'pimee_catalog_rule.form.edit.actions.clear_associations.title'
      )}
      helper={translate('pimee_catalog_rule.form.helper.clear_attributes')}
      legend={translate('pimee_catalog_rule.form.helper.clear_attributes')}
      handleDelete={handleDelete}
    />
  );
};

export { ClearAssociationsActionLine };
