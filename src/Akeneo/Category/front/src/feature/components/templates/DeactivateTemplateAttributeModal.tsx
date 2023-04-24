import {useTranslate} from '@akeneo-pim-community/shared';
import {Button, DeleteIllustration, Helper, Modal} from 'akeneo-design-system';
import {useQueryClient} from 'react-query';
import styled from 'styled-components';
import {useDeactivateTemplateAttribute} from '../../hooks/useDeactivateTemplateAttribute';

type Props = {
  templateUuid: string;
  attribute: {uuid: string; label: string};
  onClose: () => void;
};

export const DeactivateTemplateAttributeModal = ({templateUuid, attribute, onClose}: Props) => {
  const translate = useTranslate();
  const queryClient = useQueryClient();

  const deactivateTemplateAttribute = useDeactivateTemplateAttribute(templateUuid, attribute);
  const handleDeactivateTemplateAttribute = async () => {
    await deactivateTemplateAttribute();
    await queryClient.invalidateQueries(['template', templateUuid]);
    onClose();
  };

  return (
    <Modal illustration={<DeleteIllustration />} closeTitle={translate('pim_common.close')} onClose={onClose}>
      <Modal.SectionTitle color="brand">
        {translate('akeneo.category.template.delete_attribute.delete')}
      </Modal.SectionTitle>
      <Modal.Title>{translate('akeneo.category.template.delete_attribute.confirmation_modal.title')}</Modal.Title>
      <Content>
        {translate('akeneo.category.template.delete_attribute.confirmation_modal.message', {
          attribute: attribute.label,
        })}
      </Content>
      <Helper level="error">{translate('akeneo.category.template.delete_attribute.confirmation_modal.helper')}</Helper>
      <Modal.BottomButtons>
        <Button level="tertiary" onClick={onClose}>
          {translate('pim_common.cancel')}
        </Button>
        <Button level="danger" onClick={handleDeactivateTemplateAttribute}>
          {translate('pim_common.delete')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

const Content = styled.div`
  padding-bottom: 20px;
`;
