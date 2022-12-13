import React, {ReactNode, useCallback, useContext, useMemo} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Template} from '../../models';
import {Field, SectionTitle, TextInput} from 'akeneo-design-system';
import styled from 'styled-components';
import {EditCategoryContext} from '../providers';

type Props = {
  template: Template;
  onChangeLabel: (locale: string, label: string) => void;
};

const FormContainer = styled.div`
  margin-top: 20px;

  & > * {
    margin: 0 10px 20px 0;
  }
`;

const EditTemplatePropertiesForm = ({template, onChangeLabel}: Props) => {
  const translate = useTranslate();
  const {locales, localesFetchFailed} = useContext(EditCategoryContext);

  const findLocaleName = useCallback(
    (code: string) => {
      if (localesFetchFailed || !locales[code]) return code; // best effort
      return locales[code].label;
    },
    [locales, localesFetchFailed]
  );

  const handleChange = useMemo(
    () => (locale: string) => (label: string) => onChangeLabel(locale, label),
    [onChangeLabel]
  );

  if (localesFetchFailed) {
    // it would be unwise to display the form in this situation
    // it would lead the user to loose label values when saving
    // todo i18n ? nicer error display ?
    return <span>'Could not load information about languages, please reload the page'</span>;
  }

  // we consider the PIM activated locales as well as the locales already present in the labels
  const localeCodes = new Set([...Object.keys(locales), ...Object.keys(template.labels)]);

  // sorting locale code by their display names
  const sortedLocaleCodes = [...localeCodes.values()];
  sortedLocaleCodes.sort(function (lc1: string, lc2: string) {
    const label1 = findLocaleName(lc1);
    const label2 = findLocaleName(lc2);
    return label1.localeCompare(label2);
  });

  const labelsFields: ReactNode[] = sortedLocaleCodes.map(function (localeCode) {
    const localeName = findLocaleName(localeCode);
    const value = template.labels[localeCode] || '';

    return (
      <Field label={localeName} key={localeCode}>
        <TextInput
          // readOnly={!isGranted('pim_enrich_product_category_template')}
          readOnly={true}
          onChange={handleChange(localeCode)}
          value={value}
        />
      </Field>
    );
  });

  return (
    <FormContainer>
      <SectionTitle sticky={44}>
        <SectionTitle.Title>{translate('pim_common.code')}</SectionTitle.Title>
      </SectionTitle>
      <Field label={translate('pim_common.code')} requiredLabel={translate('pim_common.required_label')}>
        <TextInput name="code" readOnly={true} value={template.code} />
      </Field>
      <SectionTitle sticky={44}>
        <SectionTitle.Title>{translate('pim_common.label')}</SectionTitle.Title>
      </SectionTitle>
      {labelsFields}
    </FormContainer>
  );
};
export {EditTemplatePropertiesForm};
