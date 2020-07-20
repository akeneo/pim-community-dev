import React from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import { ActionLineProps } from './ActionLineProps';
import { ActionTemplate } from './ActionTemplate';
import { ActionTitle, AknActionFormContainer } from './ActionLine';
import { StatusSelector } from '../../../../components/Selectors/StatusSelector';
import { useControlledFormInputAction } from '../../hooks';
import { SetStatusAction } from '../../../../models/actions';
import { useTranslate } from '../../../../dependenciesTools/hooks';

const SetStatusActionLine: React.FC<ActionLineProps & {
  action: SetStatusAction;
}> = ({ handleDelete, lineNumber }) => {
  const translate = useTranslate();
  const { getValues } = useFormContext();

  const {
    fieldFormName,
    typeFormName,
    valueFormName,
    getValueFormValue,
    setValueFormValue,
  } = useControlledFormInputAction<boolean>(lineNumber);
  React.useEffect(() => {
    console.log('getValues', getValues());
  }, [JSON.stringify(getValues())]);

  const handleChange = (value: boolean) => {
    setValueFormValue(value);
  };

  return (
    <>
      <Controller
        name={fieldFormName}
        as={<input type='hidden' />}
        defaultValue='enabled'
      />
      <Controller
        name={typeFormName}
        as={<input type='hidden' />}
        defaultValue='set'
      />
      <Controller
        name={valueFormName}
        as={<span hidden />}
        defaultValue={getValueFormValue()}
        rules={{
          // We can not use 'required' validation rule a value can be "false" (for boolean).
          validate: value => {
            return ![true, false].includes(value)
              ? translate('pimee_catalog_rule.exceptions.required_value')
              : true;
          },
        }}
      />
      <ActionTemplate
        lineNumber={lineNumber}
        title={translate(
          'pimee_catalog_rule.form.edit.actions.set_status.title'
        )}
        helper={translate(
          'pimee_catalog_rule.form.edit.actions.set_status.helper'
        )}
        legend={translate(
          'pimee_catalog_rule.form.edit.actions.set_status.helper'
        )}
        handleDelete={handleDelete}>
        <ActionTitle>
          {translate(
            'pimee_catalog_rule.form.edit.actions.set_status.subtitle'
          )}
        </ActionTitle>
        <AknActionFormContainer>
          <StatusSelector
            name={valueFormName}
            label={`${translate('pim_common.status')} ${translate(
              'pim_common.required_label'
            )}`}
            placeholder={translate(
              'pimee_catalog_rule.form.edit.actions.set_status.placeholder'
            )}
            value={getValueFormValue()}
            onChange={(value: boolean) => {
              handleChange(value);
            }}
          />
        </AknActionFormContainer>
      </ActionTemplate>
    </>
  );
};

export { SetStatusActionLine };
