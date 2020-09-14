import React from 'react';
import { ActionLineProps } from './ActionLineProps';
import { useTranslate } from '../../../../dependenciesTools/hooks';
import { ActionTemplate } from './ActionTemplate';
import { Controller } from 'react-hook-form';
import { ActionCategoriesSelector } from './ActionCategoriesSelector';
import { useControlledFormInputAction } from '../../hooks';
import { CategoryCode } from '../../../../models';

const SetCategoriesActionLine: React.FC<ActionLineProps> = ({
  lineNumber,
  currentCatalogLocale,
  handleDelete,
}) => {
  const translate = useTranslate();

  const {
    fieldFormName,
    typeFormName,
    valueFormName,
    setValueFormValue,
    getValueFormValue,
  } = useControlledFormInputAction<CategoryCode[]>(lineNumber);

  return (
    <>
      <Controller
        as={<input type='hidden' />}
        name={fieldFormName}
        defaultValue='categories'
      />
      <Controller
        as={<input type='hidden' />}
        name={typeFormName}
        defaultValue='set'
      />

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
        handleDelete={handleDelete}
        lineNumber={lineNumber}>
        <ActionCategoriesSelector
          lineNumber={lineNumber}
          currentCatalogLocale={currentCatalogLocale}
          setValue={setValueFormValue}
          values={getValueFormValue() ?? []}
          valueFormName={valueFormName}
        />
      </ActionTemplate>
    </>
  );
};

export { SetCategoriesActionLine };
