import React from 'react';
import { useTranslate } from '../../../../../dependenciesTools/hooks';
import { useFormContext } from 'react-hook-form';
import { InputText } from '../../../../../components/Inputs';
import { InputValueProps } from './AttributeValue';
import { useUnregisterAtUnmount } from '../../../hooks/useUnregisterAtUnmount';

const FallbackValue: React.FC<InputValueProps> = ({
  id,
  name,
  validation,
  value,
  label,
}) => {
  const translate = useTranslate();
  const { register } = useFormContext();
  useUnregisterAtUnmount(name);

  return (
    <>
      <div>
        {translate('pimee_catalog_rule.form.edit.unhandled_attribute_type')}
      </div>
      <InputText
        data-testid={id}
        name={name}
        label={
          label ||
          `${translate('pimee_catalog_rule.rule.value')} ${translate(
            'pim_common.required_label'
          )}`
        }
        ref={register(validation || {})}
        hiddenLabel
        defaultValue={value}
        disabled
        readOnly
      />
    </>
  );
};

export { FallbackValue };
