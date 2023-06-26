import {NotificationLevel, useNotify, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {Button, Modal, ProductCategoryIllustration} from 'akeneo-design-system';
import {useMutation, useQueryClient} from 'react-query';
import {apiFetch} from '../../../tools/apiFetch';
import styled from 'styled-components';

type Props = {
  templateId: string;
  onClose: () => void;
  onSuccess?: () => void;
};

export const LoadAttributeSetModal = ({templateId, onClose, onSuccess}: Props) => {
  const translate = useTranslate();
  const queryClient = useQueryClient();
  const notify = useNotify();

  const url = useRoute('pim_category_template_rest_load_attribute_set', {templateUuid: templateId});
  const mutation = useMutation(() => apiFetch(url, {method: 'POST'}), {
    onSuccess: async () => {
      await queryClient.invalidateQueries('get-template');
      notify(NotificationLevel.SUCCESS, translate('akeneo.category.template.load_attribute_set.notification.success'));
      onClose();
      onSuccess && onSuccess();
    },
  });

  const handleLoad = () => {
    mutation.mutate();
  };

  const handleClose = () => {
    if (mutation.isLoading) {
      return;
    }
    onClose();
  };

  return (
    <Modal
      illustration={<ProductCategoryIllustration />}
      onClose={handleClose}
      closeTitle={translate('pim_common.close')}
    >
      <Modal.SectionTitle color="brand">
        {translate('akeneo.category.template.load_attribute_set.section_title')}
      </Modal.SectionTitle>
      <Modal.Title>{translate('akeneo.category.template.load_attribute_set.title')}</Modal.Title>
      {translate('akeneo.category.template.load_attribute_set.description')}
      <br />
      <br />
      {translate('akeneo.category.template.load_attribute_set.content_description')}
      <List>
        <ListItem>{translate('akeneo.category.template.load_attribute_set.content.description_attributes')}</ListItem>
        <ListItem>{translate('akeneo.category.template.load_attribute_set.content.url_attributes')}</ListItem>
        <ListItem>{translate('akeneo.category.template.load_attribute_set.content.image_attributes')}</ListItem>
        <ListItem>{translate('akeneo.category.template.load_attribute_set.content.seo_attributes')}</ListItem>
      </List>
      <br />
      {translate('akeneo.category.template.load_attribute_set.confirmation_message')}
      <Modal.BottomButtons>
        <Button level="tertiary" onClick={handleClose} disabled={mutation.isLoading}>
          {translate('pim_common.cancel')}
        </Button>
        <Button level="primary" onClick={handleLoad} disabled={mutation.isLoading}>
          {translate('akeneo.category.template.load_attribute_set.button.load')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

const List = styled.ul`
  padding: 0;
  margin-left: 1.5rem;
`;

const ListItem = styled.li`
  list-style: disc;
`;