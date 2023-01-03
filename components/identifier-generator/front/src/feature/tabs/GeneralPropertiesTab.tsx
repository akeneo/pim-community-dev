import React, {useCallback} from 'react';
import {IdentifierGenerator, LabelCollection} from '../models';
import {Field, SectionTitle, TextInput} from 'akeneo-design-system';
import {IdentifierAttributeSelector, LabelTranslations, TabValidationErrors} from '../components';
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
        <IdentifierAttributeSelector code={generator.target || ''} />
      </Styled.FormContainer>
      <LabelTranslations labelCollection={generator.labels} onLabelsChange={onLabelChange} />
    </>
  );
};

export {GeneralPropertiesTab};
