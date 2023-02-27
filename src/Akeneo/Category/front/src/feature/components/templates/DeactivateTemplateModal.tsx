import {useTranslate} from '@akeneo-pim-community/shared';
import {Button, DeleteIllustration, Helper, Modal} from 'akeneo-design-system';
import {useDeactivateTemplate} from '../../hooks/useDeactivateTemplate';

type Props = {
  template: {id: string; label: string};
  onClose: () => void;
};

export const DeactivateTemplateModal = ({template, onClose}: Props) => {
  const translate = useTranslate();
  const deactivateTemplate = useDeactivateTemplate(template);

  const handleDeactivateTemplate = () => deactivateTemplate();

  return (
    <Modal illustration={<DeleteIllustration />} onClose={onClose} closeTitle={translate('pim_common.close')}>
      <Modal.SectionTitle color="brand">
        {translate('akeneo.category.template.deactivate.deactivate_template')}
      </Modal.SectionTitle>
      <Modal.Title>{translate('akeneo.category.template.deactivate.confirmation_modal.title')}</Modal.Title>

      <p>{translate('akeneo.category.template.deactivate.confirmation_modal.message', {template: template.label})}</p>

      <Helper level="error">{translate('akeneo.category.template.deactivate.confirmation_modal.helper')}</Helper>

      <Modal.BottomButtons>
        <Button level="tertiary" onClick={onClose}>
          {translate('pim_common.cancel')}
        </Button>
        <Button level="danger" onClick={handleDeactivateTemplate}>
          {translate('akeneo.category.template.deactivate.confirmation_modal.deactivate')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};
