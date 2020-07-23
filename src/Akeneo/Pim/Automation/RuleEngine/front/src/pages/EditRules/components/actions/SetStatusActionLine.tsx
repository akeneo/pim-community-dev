import React from 'react';
import { Controller } from 'react-hook-form';
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

  const {
    fieldFormName,
    typeFormName,
    valueFormName,
    getValueFormValue,
    setValueFormValue,
  } = useControlledFormInputAction<boolean>(lineNumber);

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
            return (
              typeof value === 'boolean' ||
              translate('pimee_catalog_rule.exceptions.required_value')
            );
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
            id={`edit-rules-actions-${lineNumber}-value`}
            name={valueFormName}
            label={`${translate('pim_common.status')} ${translate(
              'pim_common.required_label'
            )}`}
            placeholder={translate(
              'pimee_catalog_rule.form.edit.actions.set_status.placeholder'
            )}
            value={getValueFormValue()}
            onChange={setValueFormValue}
          />
        </AknActionFormContainer>
      </ActionTemplate>
    </>
  );
};

export { SetStatusActionLine };
