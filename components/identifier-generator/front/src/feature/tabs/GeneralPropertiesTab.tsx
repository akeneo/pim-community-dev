import React, {useCallback} from 'react';
import {IdentifierGenerator, LabelCollection, Target, TextTransformation} from '../models';
import {Field, SectionTitle, TextInput} from 'akeneo-design-system';
import {
  IdentifierAttributeSelector,
  LabelTranslations,
  TabValidationErrors,
  TextTransformationSelector,
} from '../components';
import {Styled} from '../components/Styled';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Violation} from '../validators';

type GeneralPropertiesProps = {
  generator: IdentifierGenerator;
  onGeneratorChange: (generator: IdentifierGenerator) => void;
  validationErrors: Violation[];
};

const GeneralPropertiesTab: React.FC<GeneralPropertiesProps> = ({generator, onGeneratorChange, validationErrors}) => {
  const translate = useTranslate();

  const onLabelChange = useCallback(
    (labels: LabelCollection) => onGeneratorChange({...generator, labels}),
    [onGeneratorChange, generator]
  );

  const onTextTransformationChange = useCallback(
    (text_transformation: TextTransformation) => onGeneratorChange({...generator, text_transformation}),
    [onGeneratorChange, generator]
  );

  const onTargetChange = useCallback(
    (target: Target) => onGeneratorChange({...generator, target}),
    [onGeneratorChange, generator]
  );

  return (
    <>
      <TabValidationErrors errors={validationErrors} />
      <SectionTitle>
        <SectionTitle.Title>{translate('pim_identifier_generator.general.title')}</SectionTitle.Title>
      </SectionTitle>
      <Styled.FormContainer>
        <Field label={translate('pim_common.code')}>
          <TextInput value={generator.code} readOnly={true} />
        </Field>
        <IdentifierAttributeSelector code={generator.target || ''} onChange={onTargetChange} />
        <Field label={translate('pim_identifier_generator.general.text_transformation.label')}>
          <TextTransformationSelector value={generator.text_transformation} onChange={onTextTransformationChange} />
        </Field>
      </Styled.FormContainer>
      <LabelTranslations labelCollection={generator.labels} onLabelsChange={onLabelChange} />
    </>
  );
};

export {GeneralPropertiesTab};
