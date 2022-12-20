import React, {ReactNode, useCallback, useContext, useMemo} from 'react';
import {useSecurity, useTranslate} from '@akeneo-pim-community/shared';
import {EnrichCategory} from '../models';
import {Field, SectionTitle, TextInput} from 'akeneo-design-system';
import styled from 'styled-components';
import {EditCategoryContext} from './providers';

type Props = {
  category: EnrichCategory;
  onChangeLabel: (locale: string, label: string) => void;
};

const FormContainer = styled.div`
  margin-top: 20px;

  & > * {
    margin: 0 10px 20px 0;
  }
`;

// const ErrorMessage = styled(Helper)`
//   margin: 20px 0 0 0;
// `;

const EditPropertiesForm = ({category, onChangeLabel}: Props) => {
  const translate = useTranslate();
  const {isGranted} = useSecurity();

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

  const {
    properties: {code: categoryCode, labels: categoryLabels},
  } = category;

  // we consider the PIM activated locales as well as the locales already present in the labels
  const localeCodes = new Set([...Object.keys(locales), ...Object.keys(categoryLabels)]);

  // sorting locale code by their display names
  const sortedLocaleCodes = [...localeCodes.values()];
  sortedLocaleCodes.sort(function (lc1: string, lc2: string) {
    const label1 = findLocaleName(lc1);
    const label2 = findLocaleName(lc2);
    return label1.localeCompare(label2);
  });

  const labelsFields: ReactNode[] = sortedLocaleCodes.map(function (localeCode) {
    const localeName = findLocaleName(localeCode);
    const value = categoryLabels[localeCode] || '';

    return (
      <Field label={localeName} key={localeCode}>
        <TextInput
          //name={`${labelField}-${locale}`}
          readOnly={!isGranted('pim_enrich_product_category_edit')}
          onChange={handleChange(localeCode)}
          value={value}
        />
      </Field>
    );
  });

  return (
    <FormContainer>
      {/*{formData.errors.map((errorMessage, key) => {*/}
      {/*  return (*/}
      {/*    <ErrorMessage level="error" key={`error-${key}`}>*/}
      {/*      {errorMessage}*/}
      {/*    </ErrorMessage>*/}
      {/*  );*/}
      {/*})}*/}
      <SectionTitle sticky={44}>
        <SectionTitle.Title>{translate('pim_common.code')}</SectionTitle.Title>
      </SectionTitle>
      <Field label={translate('pim_common.code')} requiredLabel={translate('pim_common.required_label')}>
        <TextInput name="code" readOnly={true} value={categoryCode} />
      </Field>
      <SectionTitle sticky={44}>
        <SectionTitle.Title>{translate('pim_common.label')}</SectionTitle.Title>
      </SectionTitle>
      {labelsFields}
    </FormContainer>
  );
};
export {EditPropertiesForm};
