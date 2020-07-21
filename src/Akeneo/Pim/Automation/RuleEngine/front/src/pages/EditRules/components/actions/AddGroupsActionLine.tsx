import React from 'react';
import { Controller } from 'react-hook-form';
import { ActionLineProps } from './ActionLineProps';
import { useTranslate } from '../../../../dependenciesTools/hooks';
import { ActionTemplate } from './ActionTemplate';
import { useControlledFormInputAction } from '../../hooks';
import { GroupCode } from '../../../../models';

const AddGroupsActionLine: React.FC<ActionLineProps> = ({
  lineNumber,
  handleDelete,
}) => {
  const translate = useTranslate();

  const { fieldFormName, typeFormName } = useControlledFormInputAction<
    GroupCode[]
  >(lineNumber);

  return (
    <>
      <Controller
        as={<input type='hidden' />}
        name={fieldFormName}
        defaultValue='groups'
      />
      <Controller
        as={<input type='hidden' />}
        name={typeFormName}
        defaultValue='add'
      />
      <ActionTemplate
        title={translate(
          'pimee_catalog_rule.form.edit.actions.add_groups.title'
        )}
        helper={translate(
          'pimee_catalog_rule.form.edit.actions.add_groups.helper'
        )}
        legend={translate(
          'pimee_catalog_rule.form.edit.actions.add_groups.helper'
        )}
        handleDelete={handleDelete}
        lineNumber={lineNumber}></ActionTemplate>
    </>
  );
};

export { AddGroupsActionLine };
