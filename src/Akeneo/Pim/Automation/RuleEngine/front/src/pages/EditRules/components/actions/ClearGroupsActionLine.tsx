import React from 'react';
import {Controller} from 'react-hook-form';
import {useTranslate} from '../../../../dependenciesTools/hooks';
import {ActionTemplate} from './ActionTemplate';
import {useControlledFormInputAction} from '../../hooks';
import {ActionLineProps} from './ActionLineProps';

const ClearGroupsActionLine: React.FC<ActionLineProps> = ({
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
        defaultValue='groups'
      />
      <Controller
        name={typeFormName}
        as={<input type='hidden' />}
        defaultValue='clear'
      />
      <ActionTemplate
        title={translate(
          'pimee_catalog_rule.form.edit.actions.clear_groups.title'
        )}
        helper={translate('pimee_catalog_rule.form.helper.clear_groups')}
        legend={translate('pimee_catalog_rule.form.helper.clear_groups')}
        handleDelete={handleDelete}
        lineNumber={lineNumber}
      />
    </>
  );
};

export {ClearGroupsActionLine};
