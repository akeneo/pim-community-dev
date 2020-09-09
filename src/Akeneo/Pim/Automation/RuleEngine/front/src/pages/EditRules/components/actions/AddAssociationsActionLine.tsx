import React from 'react';
import { Controller } from 'react-hook-form';
import {
  AssociationValue,
  AddAssociationsAction,
} from '../../../../models/actions';
import { ActionLineProps } from './ActionLineProps';
import { useControlledFormInputAction } from '../../hooks';
import { ActionTemplate } from './ActionTemplate';
import { useTranslate } from '../../../../dependenciesTools/hooks';
import { AssociationTypeCode } from '../../../../models';
import { AssociationTypesSelector } from './association/AssociationTypesSelector';

type Props = {
  action?: AddAssociationsAction;
} & ActionLineProps;

export type Target = 'products' | 'product_models' | 'groups';

export type AssociationTarget = {
  associationTypeCode: AssociationTypeCode;
  target: Target;
};

const AddAssociationsActionLine: React.FC<Props> = ({
  lineNumber,
  handleDelete,
}) => {
  const translate = useTranslate();

  const {
    fieldFormName,
    typeFormName,
    itemsFormName,
    getItemsFormValue,
    isFormFieldInError,
  } = useControlledFormInputAction<AssociationValue>(lineNumber);

  return (
    <>
      <Controller
        as={<input type='hidden' />}
        name={fieldFormName}
        defaultValue='associations'
        rules={{
          validate: _ =>
            Object.keys(getItemsFormValue() || {}).length
              ? true
              : translate('pimee_catalog_rule.exceptions.required'),
        }}
      />
      <Controller
        as={<input type='hidden' />}
        name={typeFormName}
        defaultValue='add'
      />
      <ActionTemplate
        title={translate(
          'pimee_catalog_rule.form.edit.actions.add_associations.title'
        )}
        helper={translate(
          'pimee_catalog_rule.form.edit.actions.add_associations.helper'
        )}
        legend={translate(
          'pimee_catalog_rule.form.edit.actions.add_associations.helper'
        )}
        handleDelete={handleDelete}
        lineNumber={lineNumber}>
        <Controller
          as={AssociationTypesSelector}
          name={itemsFormName}
          value={getItemsFormValue() ?? {}}
          hasError={isFormFieldInError('field')}
        />
      </ActionTemplate>
    </>
  );
};

export { AddAssociationsActionLine };
