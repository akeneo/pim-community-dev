import {Button, Checkbox, Field, Helper, SectionTitle, TextInput, useBooleanState} from 'akeneo-design-system';
import {Attribute} from '../../models';
import {
  LabelCollection,
  NotificationLevel,
  useFeatureFlags,
  useNotify,
  userContext,
  useTranslate,
} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {DeactivateTemplateAttributeModal} from './DeactivateTemplateAttributeModal';
import {useUpdateTemplateAttribute} from '../../hooks/useUpdateTemplateAttribute';
import {getLabelFromAttribute} from '../attributes';
import {useCatalogLocales} from '../../hooks/useCatalogLocales';
import React, {useContext} from 'react';
import {BadRequestError} from '../../tools/apiFetch';
import {useDebounceCallback} from '../../tools/useDebounceCallback';
import {useQueryClient} from 'react-query';
import {useSaveStatusContext} from '../../hooks/useSaveStatusContext';
import {Status} from '../providers/SaveStatusProvider';
import {CanLeavePageContext} from '../providers';

type Props = {
  attribute: Attribute;
  activatedCatalogLocales: string[];
  translationsFormData: LabelCollection;
  onTranslationsChange: (locale: string, value: string) => void;
  translationErrors: {[locale: string]: string[]} | {};
  onTranslationErrorsChange: (locale: string, errors: string[]) => void;
  onChangeFormStatus: (attributeUuid: string, inError: boolean) => void;
};

type ResponseError = {
  error: {
    property: string;
    message: string;
  };
};

type ApiResponseError = ResponseError[];

export const AttributeSettings = ({
  attribute,
  activatedCatalogLocales,
  translationsFormData,
  onTranslationsChange,
  translationErrors,
  onTranslationErrorsChange,
  onChangeFormStatus,
}: Props) => {
  const translate = useTranslate();
  const attributeLabel = getLabelFromAttribute(attribute, userContext.get('catalogLocale'));
  const catalogLocales = useCatalogLocales();
  const featureFlag = useFeatureFlags();
  const updateTemplateAttribute = useUpdateTemplateAttribute(attribute.template_uuid, attribute.uuid);
  const saveStatusContext = useSaveStatusContext();
  const notify = useNotify();

  const [
    isDeactivateTemplateAttributeModalOpen,
    openDeactivateTemplateAttributeModal,
    closeDeactivateTemplateAttributeModal,
  ] = useBooleanState(false);
  const displayError = (errorMessages: string[], key: string) => {
    return errorMessages.map(message => {
      return (
        <Helper level="error" key={key}>
          {message}
        </Helper>
      );
    });
  };

  const queryClient = useQueryClient();

  const {setCanLeavePage, setLeavePageMessage} = useContext(CanLeavePageContext);
  const updateCanLeavePageStatuses = (saved: boolean) => {
    if (saveStatusContext.globalStatus !== Status.ERRORS) {
      setCanLeavePage(saved);
      setLeavePageMessage(translate('akeneo.category.template.attribute.settings.unsaved_changes'));
    }
  };
  
  const handleRichTextAreaChange = async () => {
    updateCanLeavePageStatuses(false);
    await updateTemplateAttribute({isRichTextArea: !(attribute.type === 'richtext')});
    updateCanLeavePageStatuses(true);
    await queryClient.invalidateQueries(['get-template', attribute.template_uuid]);
  };
  const debouncedUpdateTemplateAttribute = useDebounceCallback(
    (attributeUuid: string, locale: string, value: string) => {
      saveStatusContext.handleStatusListChange(buildStatusId(attribute.uuid, locale), Status.SAVING);
      updateTemplateAttribute({labels: {[locale]: value}})
        .then(() => {
          if (undefined !== translationErrors && translationErrors[locale]) {
            delete translationErrors[locale];
            onTranslationErrorsChange(locale, []);
            onChangeFormStatus(attributeUuid, Object.keys(translationErrors).length !== 0);
          }
          saveStatusContext.handleStatusListChange(buildStatusId(attribute.uuid, locale), Status.SAVED);
          updateCanLeavePageStatuses(true);
        })
        .catch((error: BadRequestError<ApiResponseError>) => {
          saveStatusContext.handleStatusListChange(buildStatusId(attribute.uuid, locale), Status.ERRORS);
          setCanLeavePage(false);
          setLeavePageMessage(
              `${translate('akeneo.category.template.attribute.settings.error_message')}\n${translate(
                  'akeneo.category.template.attribute.settings.unsaved_changes'
              )}`
          );
          const errors = error.data.reduce((accumulator: {[key: string]: string[]}, currentError: ResponseError) => {
            accumulator[currentError.error.property] = [currentError.error.message];

            return accumulator;
          }, {});
          onTranslationErrorsChange(locale, errors[locale]);
          onChangeFormStatus(attributeUuid, true);
          notify(
            NotificationLevel.ERROR,
            translate('akeneo.category.template.auto-save.error_notification.title'),
            translate('akeneo.category.template.auto-save.error_notification.content')
          );
        });
    },
    3000
  );
  const handleTranslationChange = (locale: string, value: string) => {
    onTranslationsChange(locale, value);
    updateCanLeavePageStatuses(false);
    debouncedUpdateTemplateAttribute(attribute.uuid, locale, value);
  };

  const buildStatusId = (attributeUuid: string, locale: string) => {
    return attributeUuid + '_' + locale;
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
          <OptionField
            checked={attribute.type === 'richtext'}
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
        {activatedCatalogLocales.map((activatedLocaleCode, index) => {
          let fieldValue = '';
          if (translationsFormData != undefined && translationsFormData[activatedLocaleCode] != undefined) {
            fieldValue = translationsFormData[activatedLocaleCode];
          } else if (attribute.labels[activatedLocaleCode] != undefined) {
            fieldValue = attribute.labels[activatedLocaleCode];
          }
          return (
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
                  saveStatusContext.handleStatusListChange(
                    buildStatusId(attribute.uuid, activatedLocaleCode),
                    Status.EDITING
                  );
                  handleTranslationChange(activatedLocaleCode, newValue);
                }}
                invalid={translationErrors !== undefined && !!translationErrors[activatedLocaleCode]}
                value={fieldValue}
              ></TextInput>
              {translationErrors !== undefined &&
                translationErrors[activatedLocaleCode] &&
                displayError(translationErrors[activatedLocaleCode], activatedLocaleCode)}
            </TranslationField>
          );
        })}
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
