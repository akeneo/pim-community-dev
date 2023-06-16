import {NotificationLevel, translate, useNotify} from '@akeneo-pim-community/shared';
import {useState} from 'react';
import {useQueryClient} from 'react-query';
import {useCreateAttribute} from '../../hooks/useCreateAttribute';
import {userContext} from '@akeneo-pim-community/shared/lib/dependencies/user-context';
import {
  Button,
  Checkbox,
  Field,
  Helper,
  Link,
  Modal,
  ProductCategoryIllustration,
  SelectInput,
  TextInput,
} from 'akeneo-design-system';
import styled from 'styled-components';

const Content = styled.div`
  padding-bottom: 20px;
`;
const FieldSet = styled.div`
  & > * {
    margin-top: 20px;
  }
`;
const HelperContent = styled(Helper)`
  min-width: 200px;
  max-width: 460px;
`;

type Form = {
  label: string;
  code: string;
  type: string;
  isScopable: boolean;
  isLocalizable: boolean;
};
type FormError = {label?: string[]; code?: string[]};

type Props = {
  templateId: string;
  onClose: () => void;
};

export const AddTemplateAttributeModal = ({templateId, onClose}: Props) => {
  const defaultCatalogLocale = userContext.get('catalog_default_locale');
  const mutation = useCreateAttribute();
  const notify = useNotify();
  const queryClient = useQueryClient();
  const [form, setForm] = useState<Form>({label: '', code: '', type: 'text', isScopable: false, isLocalizable: false});
  const [error, setError] = useState<FormError>({});

  const attributeTypes = [
    {
      type: 'text',
      label: translate('akeneo.category.template.add_attribute.confirmation_modal.input.type.option_title.text'),
    },
    {
      type: 'textarea',
      label: translate('akeneo.category.template.add_attribute.confirmation_modal.input.type.option_title.textarea'),
    },
    {
      type: 'image',
      label: translate('akeneo.category.template.add_attribute.confirmation_modal.input.type.option_title.image'),
    },
  ];

  const displayError = (errorMessages: string[]) => {
    return errorMessages.map(message => {
      return <Helper level="error">{message}</Helper>;
    });
  };

  const handleCreate = () => {
    mutation.mutate(
      {
        templateId,
        code: form.code,
        locale: defaultCatalogLocale,
        label: form.label,
        type: form.type,
        isScopable: form.isScopable,
        isLocalizable: form.isLocalizable,
      },
      {
        onError: error => {
          setError(error.data);
        },
        onSuccess: async () => {
          await queryClient.invalidateQueries(['get-template', templateId]);
          notify(NotificationLevel.SUCCESS, translate('akeneo.category.template.add_attribute.success.notification'));
          onClose();
        },
      }
    );
  };

  return (
    <Modal illustration={<ProductCategoryIllustration />} onClose={onClose} closeTitle={translate('pim_common.close')}>
      <Modal.SectionTitle color="brand">
        {translate('akeneo.category.template.add_attribute.confirmation_modal.section_title')}
      </Modal.SectionTitle>
      <Modal.Title>{translate('akeneo.category.template.add_attribute.confirmation_modal.title')}</Modal.Title>
      <Content>
        <FieldSet>
          <HelperContent level="info">
            {translate('akeneo.category.template.add_attribute.confirmation_modal.head_helper')}{' '}
            <Link href="https://help.akeneo.com/serenity-take-the-power-over-your-products/serenity-enrich-your-category">
              {translate('akeneo.category.template.add_attribute.confirmation_modal.link')}
            </Link>
          </HelperContent>
          <Field label={translate('pim_common.label')} locale={defaultCatalogLocale}>
            <TextInput
              value={form.label}
              invalid={!!error.label}
              onChange={(label: string) => {
                setForm({...form, label: label});
              }}
            />
            {error.label && displayError(error.label)}
          </Field>
          <Field label={translate('pim_common.code')} requiredLabel={translate('pim_common.required_label')}>
            <TextInput
              value={form.code}
              invalid={!!error.code}
              onChange={(code: string) => {
                setForm({...form, code: code});
              }}
            />
            {error.code && displayError(error.code)}
          </Field>
          <Field label={translate('akeneo.category.template.add_attribute.confirmation_modal.input.type.label')}>
            <SelectInput
              emptyResultLabel={translate('akeneo.category.template.add_attribute.confirmation_modal.input.type.empty')}
              openLabel={''}
              clearable={false}
              value={form.type}
              onChange={(type: string) => {
                setForm({...form, type: type});
              }}
            >
              {attributeTypes.map((attribute: {type: string; label: string}) => {
                return (
                  <SelectInput.Option title={attribute.label} value={attribute.type} key={attribute.type}>
                    {attribute.label}
                  </SelectInput.Option>
                );
              })}
            </SelectInput>
          </Field>
          <Checkbox
            checked={form.isScopable}
            onChange={(value: boolean) => {
              setForm({...form, isScopable: value});
            }}
          >
            {translate('pim_enrich.entity.attribute.property.scopable')}
          </Checkbox>
          <Checkbox
            checked={form.isLocalizable}
            onChange={(value: boolean) => {
              setForm({...form, isLocalizable: value});
            }}
          >
            {translate('pim_enrich.entity.attribute.property.localizable')}
          </Checkbox>
          <HelperContent level="info" inline>
            {translate('akeneo.category.template.add_attribute.confirmation_modal.tail_helper')}
          </HelperContent>
        </FieldSet>
      </Content>
      <Modal.BottomButtons>
        <Button level="tertiary" onClick={onClose}>
          {translate('pim_common.cancel')}
        </Button>
        <Button disabled={mutation.isLoading} level="primary" onClick={handleCreate}>
          {translate('akeneo.category.template.add_attribute.confirmation_modal.create')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};
