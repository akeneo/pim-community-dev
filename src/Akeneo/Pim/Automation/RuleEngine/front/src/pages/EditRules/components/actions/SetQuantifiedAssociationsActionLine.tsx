import React from 'react';
import { Controller } from 'react-hook-form';
import { ActionLineProps } from './ActionLineProps';
import { useControlledFormInputAction } from '../../hooks';
import { ActionTemplate } from './ActionTemplate';
import { useTranslate } from '../../../../dependenciesTools/hooks';
import { QuantifiedAssociationTypesSelector } from './association/QuantifiedAssociationTypesSelector';
import { AssociationTypeCode } from '../../../../models';
import { QuantifiedAssociationValue } from '../../../../models/actions/SetQuantifiedAssociationsAction';

export type QuantifiedTarget = 'products' | 'product_models';

export type QuantifiedAssociationTarget = {
  associationTypeCode: AssociationTypeCode;
  target: QuantifiedTarget;
};

const SetQuantifiedAssociationsActionLine: React.FC<ActionLineProps> = ({
  lineNumber,
  handleDelete,
}) => {
  const translate = useTranslate();

  const {
    fieldFormName,
    typeFormName,
    valueFormName,
    getValueFormValue,
    isFormFieldInError,
  } = useControlledFormInputAction<QuantifiedAssociationValue>(lineNumber);

  return (
    <>
      <Controller
        as={<input type='hidden' />}
        name={fieldFormName}
        defaultValue='quantified_associations'
        rules={{
          validate: _ =>
            Object.keys(getValueFormValue() || {}).length
              ? true
              : translate('pimee_catalog_rule.exceptions.required'),
        }}
      />
      <Controller
        as={<input type='hidden' />}
        name={typeFormName}
        defaultValue='set'
      />
      <ActionTemplate
        title={translate(
          'pimee_catalog_rule.form.edit.actions.set_quantified_associations.title'
        )}
        helper={translate(
          'pimee_catalog_rule.form.edit.actions.set_quantified_associations.helper'
        )}
        legend={translate(
          'pimee_catalog_rule.form.edit.actions.set_quantified_associations.helper'
        )}
        handleDelete={handleDelete}
        lineNumber={lineNumber}>
        <Controller
          as={QuantifiedAssociationTypesSelector}
          name={valueFormName}
          value={getValueFormValue() ?? {}}
          hasError={isFormFieldInError('field')}
        />
      </ActionTemplate>
    </>
  );
};

export { SetQuantifiedAssociationsActionLine };
