import React from 'react';
import { AddCategoriesAction } from '../../../../models/actions';
import { ActionLineProps } from './ActionLineProps';
import { useTranslate } from '../../../../dependenciesTools/hooks';
import { ActionTemplate } from './ActionTemplate';
import { useFormContext } from 'react-hook-form';
import { useRegisterConsts } from '../../hooks/useRegisterConst';
import { ActionCategoriesSelector } from './ActionCategoriesSelector';

type Props = {
  action: AddCategoriesAction;
} & ActionLineProps;

const AddCategoriesActionLine: React.FC<Props> = ({
  lineNumber,
  action,
  currentCatalogLocale,
  handleDelete,
}) => {
  const translate = useTranslate();
  const { control } = useFormContext();

  useRegisterConsts(
    {
      type: 'add',
      field: 'categories',
    },
    `content.actions[${lineNumber}]`
  );

  return (
    <ActionTemplate
      title={translate(
        'pimee_catalog_rule.form.edit.actions.add_category.title'
      )}
      helper={translate(
        'pimee_catalog_rule.form.edit.actions.add_category.helper'
      )}
      legend={translate(
        'pimee_catalog_rule.form.edit.actions.add_category.helper'
      )}
      handleDelete={handleDelete}>
      <ActionCategoriesSelector
        lineNumber={lineNumber}
        currentCatalogLocale={currentCatalogLocale}
        initialCategoryCodes={action.items || []}
        name={`content.actions[${lineNumber}].items`}
        defaultValue={
          control.defaultValuesRef?.current?.content?.actions[lineNumber]?.items
        }
      />
    </ActionTemplate>
  );
};

export { AddCategoriesActionLine };
