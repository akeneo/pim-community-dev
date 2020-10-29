import React from 'react';
import {Controller} from 'react-hook-form';
import {ActionLineProps} from './ActionLineProps';
import {useTranslate} from '../../../../dependenciesTools/hooks';
import {ActionTemplate} from './ActionTemplate';
import {useControlledFormInputAction} from '../../hooks';

const ClearCategoriesActionLine: React.FC<ActionLineProps> = ({
  lineNumber,
  handleDelete,
}) => {
  const translate = useTranslate();
  const {fieldFormName, typeFormName} = useControlledFormInputAction<boolean>(
    lineNumber
  );

  return (
    <>
      <Controller
        name={fieldFormName}
        as={<input type='hidden' />}
        defaultValue='categories'
      />
      <Controller
        name={typeFormName}
        as={<input type='hidden' />}
        defaultValue='clear'
      />
      <ActionTemplate
        title={translate(
          'pimee_catalog_rule.form.edit.actions.clear_categories.title'
        )}
        helper={translate('pimee_catalog_rule.form.helper.clear_categories')}
        legend={translate('pimee_catalog_rule.form.helper.clear_categories')}
        handleDelete={handleDelete}
        lineNumber={lineNumber}
      />
    </>
  );
};

export {ClearCategoriesActionLine};
