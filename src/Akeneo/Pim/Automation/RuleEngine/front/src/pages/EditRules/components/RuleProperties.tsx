import React from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import { Locale } from '../../../models';
import { InputNumber, InputText, FormSubsection } from '../../../components';
import { useTranslate } from '../../../dependenciesTools/hooks';
import InputBoolean from '../../../components/Inputs/InputBoolean';

const LABEL_MAX_LENGTH = 255;

type Props = {
  locales?: Locale[];
};

const RuleProperties: React.FC<Props> = ({ locales }) => {
  const translate = useTranslate();
  const { register, watch } = useFormContext();

  return (
    <>
      <FormSubsection
        title={translate(
          'pimee_catalog_rule.form.edit.properties.section.general'
        )}>
        <div className='AknFormContainer'>
          <div className='AknFieldContainer'>
            <InputText
              disabled
              id='edit-rules-input-code'
              name='code'
              label={translate('pim_common.code')}
              readOnly
              ref={register}
            />
          </div>
          <div className='AknFieldContainer'>
            <InputNumber
              name='priority'
              id='edit-rules-input-priority'
              label={translate('pimee_catalog_rule.form.edit.priority.label')}
              ref={register}
            />
          </div>
          <div className='AknFieldContainer'>
            <Controller
              as={InputBoolean}
              name='enabled'
              id='edit-rules-input-status'
              label={translate('pimee_catalog_rule.form.edit.status.label')}
              defaultValue={watch('enabled')}
              value={watch('enabled')}
              checkedLabel={translate(
                'pimee_catalog_rule.form.edit.status.enabled'
              )}
              uncheckedLabel={translate(
                'pimee_catalog_rule.form.edit.status.disabled'
              )}
            />
          </div>
        </div>
      </FormSubsection>
      <FormSubsection
        title={translate(
          'pimee_catalog_rule.form.edit.properties.section.labels'
        )}>
        <div className='AknFormContainer'>
          {locales &&
            locales.map(locale => {
              return (
                <div className='AknFieldContainer' key={locale.code}>
                  <InputText
                    name={`labels.${locale.code}`}
                    id={`edit-rules-input-label-${locale.code}`}
                    label={locale.label}
                    ref={register}
                    maxLength={LABEL_MAX_LENGTH}
                    withCharactersLeft
                  />
                </div>
              );
            })}
        </div>
      </FormSubsection>
    </>
  );
};

RuleProperties.displayName = 'RuleProperties';

export { RuleProperties };
