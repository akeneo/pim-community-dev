import React from 'react';
import { ActionLineProps } from './ActionLineProps';
import { useTranslate } from '../../../../dependenciesTools/hooks';
import { ActionTemplate } from './ActionTemplate';
import { Controller } from 'react-hook-form';
import { ActionCategoriesSelector } from './ActionCategoriesSelector';
import { useControlledFormInputAction } from '../../hooks';
import { CategoryCode } from '../../../../models';

const RemoveCategoriesActionLine: React.FC<ActionLineProps> = ({
  lineNumber,
  currentCatalogLocale,
  handleDelete,
}) => {
  const translate = useTranslate();

  const {
    fieldFormName,
    typeFormName,
    itemsFormName,
    setItemsFormValue,
    getItemsFormValue,
    includeChildrenFormName,
    getIncludeChildrenFormValue,
    setIncludeChildrenFormValue,
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
        defaultValue='remove'
      />
      <ActionTemplate
        title={translate(
          'pimee_catalog_rule.form.edit.actions.remove_category.title'
        )}
        helper={translate(
          'pimee_catalog_rule.form.edit.actions.remove_category.helper'
        )}
        legend={translate(
          'pimee_catalog_rule.form.edit.actions.remove_category.helper'
        )}
        handleDelete={handleDelete}
        lineNumber={lineNumber}>
        <ActionCategoriesSelector
          lineNumber={lineNumber}
          currentCatalogLocale={currentCatalogLocale}
          setValue={setItemsFormValue}
          values={getItemsFormValue() ?? []}
          valueFormName={itemsFormName}
          withIncludeChildren
          includeChildrenValue={getIncludeChildrenFormValue() ?? false}
          includeChildrenFormName={includeChildrenFormName}
          setIncludeChildrenValue={setIncludeChildrenFormValue}
        />
      </ActionTemplate>
    </>
  );
};

export { RemoveCategoriesActionLine };
