import React from 'react';
import {Delimiter} from '../../models';
import {Checkbox, Field, Helper, SectionTitle, TextInput} from 'akeneo-design-system';
import {TranslationWithLink} from '../../components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Styled} from '../../components/Styled';
import {useIdentifierGeneratorAclContext} from '../../context';

type DelimiterProps = {
  delimiter: Delimiter | null;
  onToggleDelimiter: () => void;
  onChangeDelimiter: (text: string) => void;
};

const DelimiterEdit: React.FC<DelimiterProps> = ({delimiter, onToggleDelimiter, onChangeDelimiter}) => {
  const translate = useTranslate();
  const identifierGeneratorAclContext = useIdentifierGeneratorAclContext();
  return (
    <>
      <SectionTitle>
        <SectionTitle.Title>{translate('pim_identifier_generator.structure.delimiters.title')}</SectionTitle.Title>
      </SectionTitle>
      <Helper>
        <TranslationWithLink
          translationKey={'pim_identifier_generator.structure.delimiters.helper'}
          href={'https://help.akeneo.com/pim/serenity/articles/generate-product-identifiers.html'}
          linkKey={'pim_identifier_generator.structure.delimiters.helper_link'}
        />
      </Helper>
      <Styled.CheckboxContainer>
        <Checkbox
          checked={delimiter !== null}
          onChange={onToggleDelimiter}
          readOnly={!identifierGeneratorAclContext.isManageIdentifierGeneratorAclGranted}
        >
          {translate('pim_identifier_generator.structure.delimiters.checkbox_label')}
        </Checkbox>
      </Styled.CheckboxContainer>
      {delimiter !== null && (
        <Field label={translate('pim_identifier_generator.structure.delimiters.input_label')}>
          <TextInput
            value={delimiter}
            onChange={onChangeDelimiter}
            maxLength={100}
            readOnly={!identifierGeneratorAclContext.isManageIdentifierGeneratorAclGranted}
          />
        </Field>
      )}
    </>
  );
};

export {DelimiterEdit};
