import React, {useCallback} from 'react';
import {IdentifierGenerator} from '../../../models';
import {Field, SectionTitle, TextInput} from 'akeneo-design-system';
import {IdentifierAttributeSelector} from '../IdentifierAttributeSelector';
import {Styled} from '../Styled';

type GeneralPropertiesProps = {
  generator: IdentifierGenerator;
  onGeneratorChange: (generator: IdentifierGenerator) => void;
};

const GeneralProperties: React.FC<GeneralPropertiesProps> = ({generator, onGeneratorChange}) => {
  const locales = ['en_US', 'fr_FR'];
  const defaultIdentifierCode = 'sku';

  const onLabelChange = useCallback((locale: string) => (label: string) => {
    generator.labels[locale] = label;
    onGeneratorChange({...generator});
  }, [onGeneratorChange, generator]);

  return <>
    <SectionTitle>
      <SectionTitle.Title>
        General parameters TODO
      </SectionTitle.Title>
    </SectionTitle>
    <Styled.FormContainer>
      <Field label={'Code TODO'}>
        <TextInput value={generator.code} readOnly={true} />
      </Field>
      <IdentifierAttributeSelector code={generator.target || defaultIdentifierCode}/>
    </Styled.FormContainer>
    <SectionTitle>
      <SectionTitle.Title>
        Label translations in UI locale TODO
      </SectionTitle.Title>
    </SectionTitle>
    <Styled.FormContainer>
      {locales.map(locale => (
        <Field label={`${locale} TODO`} key={locale} locale={locale}>
          <TextInput value={generator.labels[locale] || ''} onChange={onLabelChange(locale)}/>
        </Field>
      ))}
    </Styled.FormContainer>
  </>;
};

export {GeneralProperties};
