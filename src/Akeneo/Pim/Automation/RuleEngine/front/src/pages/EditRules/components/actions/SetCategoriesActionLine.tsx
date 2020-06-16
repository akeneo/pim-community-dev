import React from 'react';
import { SetCategoriesAction } from '../../../../models/actions';
import { ActionLineProps } from './ActionLineProps';
import { useTranslate } from '../../../../dependenciesTools/hooks';
import { ActionTemplate } from './ActionTemplate';
import { useFormContext } from 'react-hook-form';
import { useRegisterConsts } from '../../hooks/useRegisterConst';
import { ActionCategoriesSelector } from './ActionCategoriesSelector';

type Props = {
  action: SetCategoriesAction;
} & ActionLineProps;

const SetCategoriesActionLine: React.FC<Props> = ({
  lineNumber,
  action,
  currentCatalogLocale,
  handleDelete,
}) => {
  const translate = useTranslate();
  const { control } = useFormContext();

  useRegisterConsts(
    {
      type: 'set',
      field: 'categories',
    },
    `content.actions[${lineNumber}]`
  );

  return (
    <ActionTemplate
      title={translate(
        'pimee_catalog_rule.form.edit.actions.set_category.title'
      )}
      helper={translate(
        'pimee_catalog_rule.form.edit.actions.set_category.helper'
      )}
      legend={translate(
        'pimee_catalog_rule.form.edit.actions.set_category.helper'
      )}
      handleDelete={handleDelete}>
      <ActionCategoriesSelector
        lineNumber={lineNumber}
        currentCatalogLocale={currentCatalogLocale}
        initialCategoryCodes={action.value || []}
        name={`content.actions[${lineNumber}].value`}
        defaultValue={
          control.defaultValuesRef?.current?.content?.actions[lineNumber]?.value
        }
      />
    </ActionTemplate>
  );
};

export { SetCategoriesActionLine };
