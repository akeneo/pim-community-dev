import React, {useCallback, useState} from 'react';
import {Field, Helper, SectionTitle, TextInput} from 'akeneo-design-system';
import {Styled} from './Styled';
import {useUiLocales} from '../hooks';
import {LabelCollection} from '../models';
import {useTranslate} from '@akeneo-pim-community/shared';
import {LabelTranslationsSkeleton} from './LabelTranslationsSkeleton';
import {useIdentifierGeneratorAclContext} from '../context';

type LabelTranslationsProps = {
  labelCollection: LabelCollection;
  onLabelsChange: (labelCollection: LabelCollection) => void;
};

const LabelTranslations: React.FC<LabelTranslationsProps> = ({labelCollection, onLabelsChange}) => {
  const translate = useTranslate();
  const identifierGeneratorAclContext = useIdentifierGeneratorAclContext();
  const {data: locales = [], error, isLoading} = useUiLocales();
  const [value, setValue] = useState<LabelCollection>(labelCollection);

  const onLabelChange = useCallback(
    (locale: string) => (label: string) => {
      if (label.trim() !== '') {
        setValue({...value, [locale]: label});
        onLabelsChange({...value, [locale]: label});
      } else {
        const updatedLabels = {...value};
        delete updatedLabels[locale];
        setValue(updatedLabels);
        onLabelsChange(updatedLabels);
      }
    },
    [value, onLabelsChange]
  );

  return (
    <>
      <SectionTitle>
        <SectionTitle.Title>
          {translate('pim_identifier_generator.general.label_translations_in_ui_locale')}
        </SectionTitle.Title>
      </SectionTitle>
      <Styled.FormContainer>
        {isLoading && !error && <LabelTranslationsSkeleton />}
        {error && <Helper level="error">{translate('pim_error.general')}</Helper>}
        {locales.map(locale => (
          <Field label={locale.label} key={locale.code} locale={locale.code}>
            <TextInput
              value={value[locale.code] || ''}
              onChange={onLabelChange(locale.code)}
              readOnly={!identifierGeneratorAclContext.isManageIdentifierGeneratorAclGranted}
            />
          </Field>
        ))}
      </Styled.FormContainer>
    </>
  );
};

export {LabelTranslations};
