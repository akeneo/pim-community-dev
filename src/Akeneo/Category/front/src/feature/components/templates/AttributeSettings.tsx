import {Button, Checkbox, Field, Helper, SectionTitle, TextInput, useBooleanState} from 'akeneo-design-system';
import {Attribute} from '../../models';
import {LabelCollection, useFeatureFlags, userContext, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {DeactivateTemplateAttributeModal} from './DeactivateTemplateAttributeModal';
import {getLabelFromAttribute} from '../attributes';
import {useCatalogLocales} from '../../hooks/useCatalogLocales';
import {useState, useRef} from 'react';
import {useEditAttributeTranslations} from '../../hooks/useEditAttributeTranslations';

type Props = {
  attribute: Attribute;
  activatedCatalogLocales: string[];
};

export const AttributeSettings = ({attribute, activatedCatalogLocales}: Props) => {
  const translate = useTranslate();
  const attributeLabel = getLabelFromAttribute(attribute, userContext.get('catalogLocale'));
  const catalogLocales = useCatalogLocales();
  const featureFlag = useFeatureFlags();
  const [translations, setTranslations] = useState<LabelCollection>(attribute.labels);
  const editAttributeTranslations = useEditAttributeTranslations(attribute.template_uuid, attribute.uuid);
  const editTranslationsTimerRef = useRef<NodeJS.Timeout | null>(null);

  const [
    isDeactivateTemplateAttributeModalOpen,
    openDeactivateTemplateAttributeModal,
    closeDeactivateTemplateAttributeModal,
  ] = useBooleanState(false);

  // const displayError = (errorMessages: string[]) => {
  //     return errorMessages.map(message => {
  //         return <Helper level="error">{message}</Helper>;
  //     });
  // };

  const handleTranslationsChange = (locale: string, value: string) => {
    if (editTranslationsTimerRef.current) {
      clearTimeout(editTranslationsTimerRef.current);
    }
    setTranslations({...translations, [locale]: value});
    editTranslationsTimerRef.current = setTimeout(() => {
      editAttributeTranslations({locale, value});
      console.log(value);
    }, 2000);
  };

  return (
    <SettingsContainer>
      <SectionTitle sticky={0}>
        <SectionTitle.Title>
          {attributeLabel} {translate('akeneo.category.template.attribute.settings.title')}
        </SectionTitle.Title>
      </SectionTitle>
      <SectionTitle>
        <SectionTitle.Title level="secondary">
          {translate('akeneo.category.template.attribute.settings.options.title')}
        </SectionTitle.Title>
      </SectionTitle>
      <OptionsContainer>
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
      </OptionsContainer>
      <SectionTitle>
        <SectionTitle.Title level="secondary">
          {translate('akeneo.category.template.attribute.settings.translations.title')}
        </SectionTitle.Title>
      </SectionTitle>
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
            <TextInput
              readOnly={!featureFlag.isEnabled('category_update_template_attribute')}
              onChange={(newValue: string) => {
                handleTranslationsChange(activatedLocaleCode, newValue);
              }}
              value={translations[activatedLocaleCode] || ''}
            ></TextInput>
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

const OptionsContainer = styled.div`
  margin-bottom: 15px;
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
  margin-top: 5px;
  position: sticky;
  bottom: 0;
  background-color: #ffffff;
`;
