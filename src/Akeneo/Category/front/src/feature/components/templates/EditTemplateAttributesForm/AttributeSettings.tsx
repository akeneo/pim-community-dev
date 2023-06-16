import {useTranslate, userContext} from '@akeneo-pim-community/shared';
import {Button, Checkbox, SectionTitle, useBooleanState} from 'akeneo-design-system';
import styled from 'styled-components';
import {useCatalogActivatedLocales} from '../../../hooks/useCatalogActivatedLocales';
import {useCatalogLocales} from '../../../hooks/useCatalogLocales';
import {Attribute} from '../../../models';
import {getLabelFromAttribute} from '../../attributes';
import {DeactivateTemplateAttributeModal} from '../DeactivateTemplateAttributeModal';
import {AttributeLabelTranslationInput} from './AttributeLabelTranslationInput';
import {AttributeOptionRichTextCheckbox} from './AttributeOptionRichTextCheckbox';

type Props = {
  attribute: Attribute;
};

export const AttributeSettings = ({attribute}: Props) => {
  const translate = useTranslate();
  const attributeLabel = getLabelFromAttribute(attribute, userContext.get('catalogLocale'));

  const activatedCatalogLocales = useCatalogActivatedLocales();
  const catalogLocales = useCatalogLocales();

  const [
    isDeactivateTemplateAttributeModalOpen,
    openDeactivateTemplateAttributeModal,
    closeDeactivateTemplateAttributeModal,
  ] = useBooleanState(false);

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
      <FieldContainer>
        <AttributeOptionRichTextCheckbox attribute={attribute} />
        <Checkbox checked={attribute.is_localizable} readOnly={true}>
          {translate('akeneo.category.template.attribute.settings.options.value_per_locale')}
        </Checkbox>
        <Checkbox checked={attribute.is_scopable} readOnly={true}>
          {translate('akeneo.category.template.attribute.settings.options.value_per_channel')}
        </Checkbox>
      </FieldContainer>

      <SectionTitle>
        <SectionTitle.Title level="secondary">
          {translate('akeneo.category.template.attribute.settings.translations.title')}
        </SectionTitle.Title>
      </SectionTitle>
      <FieldContainer>
        {activatedCatalogLocales?.map(localeCode => (
          <AttributeLabelTranslationInput
            key={localeCode}
            attribute={attribute}
            localeCode={localeCode}
            label={catalogLocales?.find(catalogLocale => catalogLocale.code === localeCode)?.label || localeCode}
          />
        ))}
      </FieldContainer>

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

const FieldContainer = styled.div`
  margin-bottom: 15px;
  & > * {
    margin: 10px 0 0 0;
  }
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
