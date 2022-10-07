import React, {useCallback} from 'react';
import {IdentifierGenerator, LabelCollection} from '../../../models';
import {Field, SectionTitle, TextInput} from 'akeneo-design-system';
import {IdentifierAttributeSelector} from '../IdentifierAttributeSelector';
import {Styled} from '../Styled';
import {LabelTranslations} from './LabelTranslations';

type GeneralPropertiesProps = {
  generator: IdentifierGenerator;
  onGeneratorChange: (generator: IdentifierGenerator) => void;
};

const GeneralProperties: React.FC<GeneralPropertiesProps> = ({generator, onGeneratorChange}) => {
  const defaultIdentifierCode = 'sku';

  const onLabelChange = useCallback((labelCollection: LabelCollection) => {
    generator.labels = labelCollection;
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
    <LabelTranslations labelCollection={generator.labels} onLabelsChange={onLabelChange}/>
  </>;
};

export {GeneralProperties};
