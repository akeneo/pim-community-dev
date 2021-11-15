import React from 'react';
import {Controller} from 'react-hook-form';
import {ActionTemplate} from './ActionTemplate';
import {useTranslate} from '../../../../dependenciesTools/hooks';
import {ActionLineProps} from './ActionLineProps';
import {useControlledFormInputAction} from '../../hooks';

const ClearAssociationsActionLine: React.FC<ActionLineProps> = ({
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
        defaultValue='associations'
      />
      <Controller
        name={typeFormName}
        as={<input type='hidden' />}
        defaultValue='clear'
      />
      <ActionTemplate
        title={translate(
          'pimee_catalog_rule.form.edit.actions.clear_associations.title'
        )}
        helper={translate(
          'pimee_catalog_rule.form.edit.actions.clear_associations.helper'
        )}
        legend={translate(
          'pimee_catalog_rule.form.edit.actions.clear_associations.helper'
        )}
        handleDelete={handleDelete}
        lineNumber={lineNumber}
      />
    </>
  );
};

export {ClearAssociationsActionLine};
