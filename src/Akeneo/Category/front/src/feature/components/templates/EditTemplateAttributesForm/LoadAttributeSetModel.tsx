import {useTranslate} from '@akeneo-pim-community/shared';
import {Button, Modal, ProductCategoryIllustration} from 'akeneo-design-system';
import {useMutation, useQueryClient} from 'react-query';

type Props = {
  templateId: string;
  onClose: () => void;
};

export const LoadAttributeSetModal = ({templateId, onClose}: Props) => {
  const translate = useTranslate();
  const queryClient = useQueryClient();

  const mutation = useMutation(() => new Promise(resolve => setTimeout(resolve, 1000)), {
    onSuccess: async () => {
      await queryClient.invalidateQueries('get-template');
      onClose();
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
      <ul>
        <li>{translate('akeneo.category.template.load_attribute_set.content.description_attributes')}</li>
        <li>{translate('akeneo.category.template.load_attribute_set.content.url_attributes')}</li>
        <li>{translate('akeneo.category.template.load_attribute_set.content.image_attributes')}</li>
        <li>{translate('akeneo.category.template.load_attribute_set.content.seo_attributes')}</li>
      </ul>
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
