import {Button, Checkbox, Field, SectionTitle, TextInput, useBooleanState} from 'akeneo-design-system';
import {Attribute} from '../../models';
import {userContext, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {DeactivateTemplateAttributeModal} from './DeactivateTemplateAttributeModal';
import {getLabelFromAttribute} from '../attributes';
import {useCatalogLocales} from '../../hooks/useCatalogLocales';

type Props = {
  attribute: Attribute;
  activatedCatalogLocales: string[];
};

export const AttributeSettings = ({attribute, activatedCatalogLocales}: Props) => {
  const translate = useTranslate();
  const attributeLabel = getLabelFromAttribute(attribute, userContext.get('catalogLocale'));
  const catalogLocales = useCatalogLocales();

  const [
    isDeactivateTemplateAttributeModalOpen,
    openDeactivateTemplateAttributeModal,
    closeDeactivateTemplateAttributeModal,
  ] = useBooleanState(false);

  return (
    <SettingsContainer>
      <StyledSectionTitle sticky={0}>
        <SectionTitle.Title>
          {attributeLabel} {translate('akeneo.category.template.attribute.settings.title')}
        </SectionTitle.Title>
      </StyledSectionTitle>
      <StyledSectionTitle sticky={0}>
        <SectionTitle.Title level="secondary">
          {translate('akeneo.category.template.attribute.settings.options.title')}
        </SectionTitle.Title>
      </StyledSectionTitle>
      <div>
        {['textarea', 'richtext'].includes(attribute.type) && (
          <OptionField checked={attribute.type === 'richtext'} readOnly={true}>
            {translate('akeneo.category.template.attribute.settings.options.rich_text')}
          </OptionField>
        )}
        <OptionField checked={attribute.is_localizable} readOnly={true}>
          {translate('akeneo.category.template.attribute.settings.options.value_per_locale')}
        </OptionField>
        <OptionField checked={attribute.is_scopable} readOnly={true}>
          {translate('akeneo.category.template.attribute.settings.options.value_per_channel')}
        </OptionField>
      </div>
      <StyledSectionTitle sticky={0}>
        <SectionTitle.Title level="secondary">
          {translate('akeneo.category.template.attribute.settings.translations.title')}
        </SectionTitle.Title>
      </StyledSectionTitle>
      <div>
        {activatedCatalogLocales.map((activatedLocaleCode, index) => (
          <TranslationField
            label={
              catalogLocales?.find(catalogLocale => catalogLocale.code === activatedLocaleCode)?.label ||
              activatedLocaleCode
            }
            locale={activatedLocaleCode}
            key={activatedLocaleCode}
          >
            <TextInput readOnly onChange={() => {}} value={attribute.labels[activatedLocaleCode] || ''}></TextInput>
          </TranslationField>
        ))}
      </div>
      <Footer>
        <Button level="danger" ghost onClick={openDeactivateTemplateAttributeModal}>
          {translate('akeneo.category.template.attribute.delete_button')}
        </Button>
        {isDeactivateTemplateAttributeModalOpen && (
          <DeactivateTemplateAttributeModal
            templateUuid={attribute.template_uuid}
            attribute={{uuid: attribute.uuid, label: attributeLabel}}
            onClose={closeDeactivateTemplateAttributeModal}
          />
        )}
      </Footer>
    </SettingsContainer>
  );
};

const SettingsContainer = styled.div`
  display: flex;
  flex-direction: column;
  padding-left: 40px;
  width: 510px;
  overflow-y: auto;
`;

const StyledSectionTitle = styled(SectionTitle)`
  margin-top: 10px;
`;

const OptionField = styled(Checkbox)`
  margin: 10px 0 0 0;
`;

const TranslationField = styled(Field)`
  margin: 20px 0 0 0;
`;

const Footer = styled.div`
  display: flex;
  flex-direction: row-reverse;
  padding: 5px 0 5px;
  margin-top: 2px;
  position: sticky;
  bottom: 0;
  background-color: #ffffff;
`;
