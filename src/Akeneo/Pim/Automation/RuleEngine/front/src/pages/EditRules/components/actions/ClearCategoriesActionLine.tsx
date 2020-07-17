import React from 'react';
import { ClearCategoriesAction } from '../../../../models/actions';
import { ActionLineProps } from './ActionLineProps';
import { useTranslate } from '../../../../dependenciesTools/hooks';
import { useRegisterConst } from '../../hooks/useRegisterConst';
import { ActionTemplate } from './ActionTemplate';

type Props = {
  action: ClearCategoriesAction;
} & ActionLineProps;

const ClearCategoriesActionLine: React.FC<Props> = ({
  lineNumber,
  action,
  handleDelete,
}) => {
  const translate = useTranslate();
  useRegisterConst(`content.actions[${lineNumber}]`, action);

  return (
    <ActionTemplate
      title={translate(
        'pimee_catalog_rule.form.edit.actions.clear_categories.title'
      )}
      helper={translate('pimee_catalog_rule.form.helper.clear_categories')}
      legend={translate('pimee_catalog_rule.form.helper.clear_categories')}
      handleDelete={handleDelete}
      lineNumber={lineNumber}
    />
  );
};

export { ClearCategoriesActionLine };
