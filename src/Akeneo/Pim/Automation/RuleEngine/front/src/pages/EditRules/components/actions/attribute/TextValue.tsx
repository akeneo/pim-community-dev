import React from 'react';
import {
  useTranslate,
  useUserCatalogLocale,
} from '../../../../../dependenciesTools/hooks';
import { useFormContext } from 'react-hook-form';
import { InputText } from '../../../../../components/Inputs';
import { InputValueProps, ValueModuleGuesser } from './AttributeValue';
import { getAttributeLabel } from '../../../../../models';
import { useUnregisterAtUnmount } from '../../../hooks/useUnregisterAtUnmount';

const TextValue: React.FC<InputValueProps> = ({
  id,
  attribute,
  name,
  validation,
  value,
  label,
  onChange,
}) => {
  const translate = useTranslate();
  const { register } = useFormContext();
  const catalogLocale = useUserCatalogLocale();
  useUnregisterAtUnmount(name);

  return (
    <InputText
      data-testid={id}
      name={name}
      label={
        label ||
        `${getAttributeLabel(attribute, catalogLocale)} ${translate(
          'pim_common.required_label'
        )}`
      }
      ref={register(validation || {})}
      defaultValue={value}
      onChange={(event: any) => onChange(event.target.value)}
    />
  );
};

const supportsTextValueModule: ValueModuleGuesser = attribute => {
  return 'pim_catalog_text' === attribute?.type ? TextValue : null;
};

export { TextValue, supportsTextValueModule };
