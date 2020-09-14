import React from 'react';
import { Controller } from 'react-hook-form';
import { ActionTemplate } from './ActionTemplate';
import { useTranslate } from '../../../../dependenciesTools/hooks';
import { ActionLineProps } from './ActionLineProps';
import { useControlledFormInputAction } from '../../hooks';

const ClearQuantifiedAssociationsActionLine: React.FC<ActionLineProps> = ({
  lineNumber,
  handleDelete,
}) => {
  const translate = useTranslate();
  const { fieldFormName, typeFormName } = useControlledFormInputAction<boolean>(
    lineNumber
  );

  return (
    <>
      <Controller
        name={fieldFormName}
        as={<input type='hidden' />}
        defaultValue='quantified_associations'
      />
      <Controller
        name={typeFormName}
        as={<input type='hidden' />}
        defaultValue='clear'
      />
      <ActionTemplate
        title={translate(
          'pimee_catalog_rule.form.edit.actions.clear_quantified_associations.title'
        )}
        helper={translate(
          'pimee_catalog_rule.form.edit.actions.clear_quantified_associations.helper'
        )}
        legend={translate(
          'pimee_catalog_rule.form.edit.actions.clear_quantified_associations.helper'
        )}
        handleDelete={handleDelete}
        lineNumber={lineNumber}
      />
    </>
  );
};

export { ClearQuantifiedAssociationsActionLine };
