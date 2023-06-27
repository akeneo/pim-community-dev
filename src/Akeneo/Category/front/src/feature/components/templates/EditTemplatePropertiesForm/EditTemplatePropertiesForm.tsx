import {useTranslate} from '@akeneo-pim-community/shared';
import {Field, SectionTitle, SkeletonPlaceholder, TextInput} from 'akeneo-design-system';
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
  const sortedUiLocales = uiLocales?.sort((a, b) => a.label.localeCompare(b.label));

  return (
    <>
      <SectionTitle>
        <SectionTitle.Title>{translate('akeneo.category.template.properties.general_properties')}</SectionTitle.Title>
      </SectionTitle>
      <FieldContainer>
        <Field label={translate('pim_common.code')}>
          <TextInput value={template.code} readOnly />
        </Field>
      </FieldContainer>

      <>
        <SectionTitle>
          <SectionTitle.Title>
            {translate('akeneo.category.template.properties.label_translations_in_ui_locales')}
          </SectionTitle.Title>
        </SectionTitle>
        <FieldContainer>
          {sortedUiLocales ? (
            sortedUiLocales.map(locale => (
              <TemplateLabelTranslationInput key={locale.code} locale={locale} template={template} />
            ))
          ) : (
            <InputListSkeleton />
          )}
        </FieldContainer>
      </>
    </>
  );
};

const FieldContainer = styled.div`
  margin-bottom: 15px;
  & > * {
    margin: 10px 0 0 0;
  }
`;

const InputListSkeleton = () => (
  <>
    {Array(15)
      .fill(0)
      .map((_, i) => (
        <SkeletonPlaceholder key={i} style={{maxWidth: 460}}>
          <Field label="label">
            <TextInput readOnly />
          </Field>
        </SkeletonPlaceholder>
      ))}
  </>
);