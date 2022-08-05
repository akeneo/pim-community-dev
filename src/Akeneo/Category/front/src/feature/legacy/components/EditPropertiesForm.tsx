import React from 'react';
import {useSecurity, useTranslate} from '@akeneo-pim-community/shared';
import {Category, EditCategoryForm} from '../models';
import {Field, SectionTitle, TextInput} from 'akeneo-design-system';
import {ErrorMessageLegacy, FormContainerLegacy} from './StyleLegacy';

type Props = {
  category: Category;
  formData: EditCategoryForm | null;
  onChangeLabel: (locale: string, label: string) => void;
};

const EditPropertiesForm = ({category, formData, onChangeLabel}: Props) => {
  const translate = useTranslate();
  const {isGranted} = useSecurity();

  if (formData === null) {
    return <></>;
  }

  return (
    <FormContainerLegacy>
      {formData.errors.map((errorMessage, key) => {
        return (
          <ErrorMessageLegacy level="error" key={`error-${key}`}>
            {errorMessage}
          </ErrorMessageLegacy>
        );
      })}
      <SectionTitle>
        <SectionTitle.Title>{translate('pim_common.code')}</SectionTitle.Title>
      </SectionTitle>
      <Field label={translate('pim_common.code')} requiredLabel={translate('pim_common.required_label')}>
        <TextInput name="code" readOnly={true} value={category.code} />
      </Field>
      <SectionTitle>
        <SectionTitle.Title>{translate('pim_common.label')}</SectionTitle.Title>
      </SectionTitle>
      {Object.entries(formData.label).map(([locale, labelField]) => (
        <Field label={labelField.label} key={locale}>
          <TextInput
            name={labelField.fullName}
            readOnly={!isGranted('pim_enrich_product_category_edit')}
            onChange={changedLabel => onChangeLabel(locale, changedLabel)}
            value={labelField.value}
          />
        </Field>
      ))}
    </FormContainerLegacy>
  );
};
export {EditPropertiesForm};
