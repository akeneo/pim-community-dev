import React from 'react';
import { useFormContext } from 'react-hook-form';
import { Translate } from '../../../dependenciesTools';
import { Locale } from '../../../models';
import { InputNumber, InputText, SmallHelper } from '../../../components';

type Props = {
  locales?: Locale[];
  translate: Translate;
};

const RuleProperties: React.FC<Props> = ({ locales, translate }) => {
  const { register } = useFormContext();
  return (
    <div className='AknFormContainer'>
      <SmallHelper>Page under construction</SmallHelper>
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
        {locales &&
          locales.map(locale => {
            return (
              <div className='AknFieldContainer' key={locale.code}>
                <InputText
                  name={`labels.${locale.code}`}
                  id={`edit-rules-input-label-${locale.code}`}
                  label={locale.label}
                  ref={register}
                />
              </div>
            );
          })}
      </div>
    </div>
  );
};

RuleProperties.displayName = 'RuleProperties';

export { RuleProperties };
