import React, {useEffect} from 'react';
import styled from 'styled-components';
import {useSecurity, useTranslate} from '@akeneo-pim-community/shared';
import {Category, EditableCategoryProperties} from '../../models';
import {Field, SectionTitle, TextInput} from 'akeneo-design-system';
import {useActivatedLocales} from '../../hooks';

const FormContainer = styled.form`
  & > * {
    margin: 0 10px 20px 0;
  }
`;

type Props = {
  category: Category;
  setEditedProperties: (properties: EditableCategoryProperties) => void;
};

const EditCategoryForm = ({category, setEditedProperties}: Props) => {
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const {locales, load: loadLocales} = useActivatedLocales();

  useEffect(() => {
    (async () => {
      await loadLocales();
    })();
  }, []);

  const onChangeLabel = (localeCode: string, label: string) => {
    const changedLabels = {...category.labels, [localeCode]: label};
    setEditedProperties({labels: changedLabels});
  };

  return (
    <FormContainer>
      <SectionTitle>
        <SectionTitle.Title>{translate('pim_common.code')}</SectionTitle.Title>
      </SectionTitle>
      <Field label={translate('pim_common.code')} requiredLabel={translate('pim_common.required_label')}>
        <TextInput name="code" readOnly={true} value={category.code} />
      </Field>
      <SectionTitle>
        <SectionTitle.Title>{translate('pim_common.label')}</SectionTitle.Title>
      </SectionTitle>
      {locales.map(locale => (
        <Field label={locale.label} key={locale.code}>
          <TextInput
            name={locale.code}
            readOnly={!isGranted('pim_enrich_product_category_edit')}
            onChange={changedLabel => onChangeLabel(locale.code, changedLabel)}
            value={category.labels.hasOwnProperty(locale.code) ? category.labels[locale.code] : ''}
          />
        </Field>
      ))}
    </FormContainer>
  );
};
export {EditCategoryForm};
