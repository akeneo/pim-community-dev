import React from 'react';
import { Controller } from 'react-hook-form';
import {
  AssociationValue,
  SetAssociationsAction,
} from '../../../../models/actions';
import { ActionLineProps } from './ActionLineProps';
import { useControlledFormInputAction } from '../../hooks';
import { ActionTemplate } from './ActionTemplate';
import { useTranslate } from '../../../../dependenciesTools/hooks';
import { AssociationTypeCode } from '../../../../models';
import { AssociationTypesSelector } from './association/AssociationTypesSelector';

type Props = {
  action?: SetAssociationsAction;
} & ActionLineProps;

export type Target = 'products' | 'product_models' | 'groups';

export type AssociationTarget = {
  associationTypeCode: AssociationTypeCode;
  target: Target;
};

const SetAssociationsActionLine: React.FC<Props> = ({
  lineNumber,
  handleDelete,
}) => {
  const translate = useTranslate();

  const {
    fieldFormName,
    typeFormName,
    getValueFormValue,
    setValueFormValue,
  } = useControlledFormInputAction<AssociationValue>(lineNumber);

  return (
    <>
      <Controller
        as={<input type='hidden' />}
        name={fieldFormName}
        defaultValue='associations'
      />
      <Controller
        as={<input type='hidden' />}
        name={typeFormName}
        defaultValue='set'
      />
      <ActionTemplate
        title={translate(
          'pimee_catalog_rule.form.edit.actions.set_associations.title'
        )}
        helper={translate(
          'pimee_catalog_rule.form.edit.actions.set_associations.helper'
        )}
        legend={translate(
          'pimee_catalog_rule.form.edit.actions.set_associations.helper'
        )}
        handleDelete={handleDelete}
        lineNumber={lineNumber}>
        <AssociationTypesSelector
          value={getValueFormValue() ?? {}}
          onChange={setValueFormValue}
        />
      </ActionTemplate>
    </>
  );
};

export { SetAssociationsActionLine };
