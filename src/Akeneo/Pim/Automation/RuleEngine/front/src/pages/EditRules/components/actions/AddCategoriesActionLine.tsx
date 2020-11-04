import React from 'react';
import {Controller} from 'react-hook-form';
import {ActionLineProps} from './ActionLineProps';
import {useTranslate} from '../../../../dependenciesTools/hooks';
import {ActionTemplate} from './ActionTemplate';
import {ActionCategoriesSelector} from './ActionCategoriesSelector';
import {useControlledFormInputAction} from '../../hooks';
import {CategoryCode} from '../../../../models';

const AddCategoriesActionLine: React.FC<ActionLineProps> = ({
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
        defaultValue='add'
      />
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
        handleDelete={handleDelete}
        lineNumber={lineNumber}>
        <ActionCategoriesSelector
          lineNumber={lineNumber}
          currentCatalogLocale={currentCatalogLocale}
          setValue={setItemsFormValue}
          values={getItemsFormValue() ?? []}
          valueFormName={itemsFormName}
          valueRequired
        />
      </ActionTemplate>
    </>
  );
};

export {AddCategoriesActionLine};
