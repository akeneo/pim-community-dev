import {translate} from '@akeneo-pim-community/shared';
import {useState} from 'react';
import {useQueryClient} from 'react-query';
import {userContext} from '@akeneo-pim-community/shared/lib/dependencies/user-context';
import {
  Button,
  Field,
  Helper,
  Link,
  Modal,
  ProductCategoryIllustration,
  TextInput,
} from 'akeneo-design-system';
import styled from 'styled-components';
import {CategoryTreeModel} from "../../models";
import {useCreateTemplate} from "../../hooks/useCreateTemplate";

type Form = {
  label: string;
  code: string;
};
type FormError = {label?: string[]; code?: string[]};

type Props = {
  categoryTree: CategoryTreeModel;
  onClose: () => void;
};

export const CreateTemplateModal = ({categoryTree, onClose}: Props) => {
  // const defaultCatalogLocale = userContext.get('catalog_default_locale');
  const defaultUserUiLocale = userContext.get('ui_locale');
  console.log('defaultUserUiLocale: ', defaultUserUiLocale);
  const mutation = useCreateTemplate();
  const queryClient = useQueryClient();
  const [form, setForm] = useState<Form>({label: '', code: ''});
  const [error, setError] = useState<FormError>({});

  const displayError = (errorMessages: string[]) => {
    return errorMessages.map(message => {
      return (
        <Helper key={message} level="error">
          {message}
        </Helper>
      );
    });
  };

  const handleCreate = () => {
    mutation.mutate(
      {
        categoryTreeId: categoryTree.id,
        code: form.code,
        locale: defaultUserUiLocale,
        label: form.label,
      },
      {
        onError: error => {
          setError(error.data);
        },
        onSuccess: () => {
          onClose();
        }
      });
  };

  return (
    <Modal illustration={<ProductCategoryIllustration />} onClose={onClose} closeTitle={translate('pim_common.close')}>
      <Modal.SectionTitle color="brand">
        {translate('akeneo.category.template.create')}
      </Modal.SectionTitle>
      <Modal.Title>{translate('akeneo.category.template.create_confirmation_modal.title')}</Modal.Title>
      <Content>
        <FieldSet>
          <HelperContent level="info">
            {translate('akeneo.category.template.create_confirmation_modal.helper')}{' '}
            <Link href="https://help.akeneo.com/serenity-take-the-power-over-your-products/serenity-enrich-your-category">
              {translate('akeneo.category.template.add_attribute.confirmation_modal.link')}
            </Link>
          </HelperContent>
          <CategoryTreeContainer>
            <div>
              {translate('akeneo.category.template.create_confirmation_modal.category_tree')}
            </div>
            <TreeLabel>
            {categoryTree.label}
            </TreeLabel>
          </CategoryTreeContainer>
          <Field label={translate('pim_common.label')} locale={defaultUserUiLocale}>
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
        </FieldSet>
      </Content>
      <Modal.BottomButtons>
        <Button level="tertiary" onClick={onClose}>
          {translate('pim_common.cancel')}
        </Button>
        <Button
          disabled={mutation.isLoading}
          level="primary"
          onClick={handleCreate}
        >
          {translate('akeneo.category.template.add_attribute.confirmation_modal.create')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

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

const CategoryTreeContainer = styled.div`
  flex-direction: column;
  align-items: flex-start;
`;

const TreeLabel = styled.div`
  color: #000000;
`;
