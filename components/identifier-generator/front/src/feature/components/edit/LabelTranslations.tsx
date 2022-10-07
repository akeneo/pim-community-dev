import React, {useCallback} from 'react';
import {Field, SectionTitle, TextInput} from 'akeneo-design-system';
import {Styled} from '../Styled';
import {useUiLocales} from '../../hooks/useUiLocales';
import {LabelCollection} from '../../../models';

type LabelTranslationsProps = {
  labelCollection: LabelCollection;
  onLabelsChange: (labelCollection: LabelCollection) => void;
}

const LabelTranslations: React.FC<LabelTranslationsProps> = ({labelCollection, onLabelsChange}) => {
  const {data: locales} = useUiLocales();

  const onLabelChange = useCallback((locale: string) => (label: string) => {
    labelCollection[locale] = label;
    onLabelsChange({...labelCollection});
  }, [labelCollection, onLabelsChange]);

  return <>
    <SectionTitle>
      <SectionTitle.Title>
        Label translations in UI locale TODO
      </SectionTitle.Title>
    </SectionTitle>
    <Styled.FormContainer>
      {(locales || []).map(locale => (
        <Field label={locale.label} key={locale.code} locale={locale.code}>
          <TextInput value={labelCollection[locale.code] || ''} onChange={onLabelChange(locale.code)}/>
        </Field>
      ))}
    </Styled.FormContainer>
  </>;
};

export {LabelTranslations};
