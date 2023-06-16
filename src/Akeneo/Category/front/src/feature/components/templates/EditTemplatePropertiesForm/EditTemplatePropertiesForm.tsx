import {useTranslate} from '@akeneo-pim-community/shared';
import {Field, SectionTitle, TextInput} from 'akeneo-design-system';
import styled from 'styled-components';
import {useUiLocales} from '../../../hooks/useUiLocales';
import {Template} from '../../../models';
import {TemplateLabelTranslationInput} from './TemplateLabelTranslationInput';

type Props = {
  template: Template;
};

export const EditTemplatePropertiesForm = ({template}: Props) => {
  const translate = useTranslate();

  const uiLocales = useUiLocales();

  return (
    <>
      <SectionTitle>
        <SectionTitle.Title>{translate('akeneo.category.template.properties.general_properties')}</SectionTitle.Title>
      </SectionTitle>
      <FieldContainer>
        <Field label={translate('pim_common.code')}>
          <TextInput value={template.code} readOnly={true} />
        </Field>
      </FieldContainer>

      {
        uiLocales && (
            <>
              <SectionTitle>
                <SectionTitle.Title>
                  {translate('akeneo.category.template.properties.label_translations_in_ui_locales')}
                </SectionTitle.Title>
              </SectionTitle>
              <FieldContainer>
                {uiLocales.map(locale => (
                  <TemplateLabelTranslationInput key={locale.code} locale={locale} template={template} />
                ))}
              </FieldContainer>
            </>
          ) &&
          null /* remove to display section */
      }
    </>
  );
};

const FieldContainer = styled.div`
  margin-bottom: 15px;
  & > * {
    margin: 10px 0 0 0;
  }
`;
