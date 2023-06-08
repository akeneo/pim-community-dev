import {Button, Checkbox, Field, Helper, SectionTitle, TextInput, useBooleanState} from 'akeneo-design-system';
import {Attribute} from '../../models';
import {LabelCollection, useFeatureFlags, userContext, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {DeactivateTemplateAttributeModal} from './DeactivateTemplateAttributeModal';
import {useUpdateTemplateAttribute} from '../../hooks/useUpdateTemplateAttribute';
import {getLabelFromAttribute} from '../attributes';
import {useCatalogLocales} from '../../hooks/useCatalogLocales';
import React, {useContext, useState} from 'react';
import {BadRequestError} from '../../tools/apiFetch';
import {useDebounceCallback} from '../../tools/useDebounceCallback';
import {Prompt} from 'react-router-dom';
import {useUnsavedChanges} from '../../hooks';
import {CanLeavePageContext} from '../providers';

type Props = {
  attribute: Attribute;
  activatedCatalogLocales: string[];
};

type ResponseError = {
  error: {
    property: string;
    message: string;
  };
};

type ApiResponseError = ResponseError[];

export const AttributeSettings = ({attribute, activatedCatalogLocales}: Props) => {
  const translate = useTranslate();
  const attributeLabel = getLabelFromAttribute(attribute, userContext.get('catalogLocale'));
  const catalogLocales = useCatalogLocales();
  const featureFlag = useFeatureFlags();
  const updateTemplateAttribute = useUpdateTemplateAttribute(attribute.template_uuid, attribute.uuid);

  const [
    isDeactivateTemplateAttributeModalOpen,
    openDeactivateTemplateAttributeModal,
    closeDeactivateTemplateAttributeModal,
  ] = useBooleanState(false);

  const displayError = (errorMessages: string[], key: string) => {
    return errorMessages.map(message => {
      return <Helper level="error">{message}</Helper>;
    });
  };

  const [isRichTextArea, setIsRichTextArea] = useState<boolean>(attribute.type === 'richtext');
  const [translations, setTranslations] = useState<LabelCollection>(attribute.labels);
  const [error, setError] = useState<{[locale: string]: string[]}>({});
  const [isSaved, setSaved, setUnsavedMessage] = useUnsavedChanges(
    translate('akeneo.category.template.attribute.settings.unsaved_changes')
  );
  const {setCanLeavePage, setLeavePageMessage} = useContext(CanLeavePageContext);

  setLeavePageMessage(translate('akeneo.category.template.attribute.settings.unsaved_changes'));

  const updateSavedStatuses = (saved: boolean) => {
    if (Object.keys(error).length === 0) {
      setSaved(saved);
      setCanLeavePage(saved);
      setLeavePageMessage(translate('akeneo.category.template.attribute.settings.unsaved_changes'));
      setUnsavedMessage(translate('akeneo.category.template.attribute.settings.unsaved_changes'));
    }
  };

  const handleRichTextAreaChange = () => {
    setIsRichTextArea(!isRichTextArea);
    updateSavedStatuses(false);
    updateTemplateAttribute({isRichTextArea: !isRichTextArea}).then(() => {
      updateSavedStatuses(true);
    });
  };
  const debouncedUpdateTemplateAttribute = useDebounceCallback((locale: string, value: string) => {
    updateTemplateAttribute({labels: {[locale]: value}})
      .then(() => {
        if (error[locale]) {
          delete error[locale];
          setError({...error});
        }
        updateSavedStatuses(true);
      })
      .catch((error: BadRequestError<ApiResponseError>) => {
        const errors = error.data.reduce((accumulator: {[key: string]: string[]}, currentError: ResponseError) => {
          accumulator[currentError.error.property] = [currentError.error.message];
          return accumulator;
        }, {});
        updateSavedStatuses(false);
        setLeavePageMessage(translate('akeneo.category.template.attribute.settings.error_message'));
        setUnsavedMessage(translate('akeneo.category.template.attribute.settings.error_message'));
        setError(state => ({...state, ...errors}));
      });
  }, 300);
  const handleTranslationsChange = (locale: string, value: string) => {
    setTranslations({...translations, [locale]: value});
    updateSavedStatuses(false);
    debouncedUpdateTemplateAttribute(locale, value);
  };

  return (
    <SettingsContainer>
      <Prompt
        when={!isSaved}
        message={() => translate('akeneo.category.template.attribute.settings.unsaved_changes')}
      />
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
          <OptionField
            checked={isRichTextArea}
            onChange={handleRichTextAreaChange}
            readOnly={!featureFlag.isEnabled('category_update_template_attribute')}
          >
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
              invalid={!!error[activatedLocaleCode]}
              value={translations[activatedLocaleCode] || ''}
            ></TextInput>
            {error[activatedLocaleCode] && displayError(error[activatedLocaleCode], activatedLocaleCode)}
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
