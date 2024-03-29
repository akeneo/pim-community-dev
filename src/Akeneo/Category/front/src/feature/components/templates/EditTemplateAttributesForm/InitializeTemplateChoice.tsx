import {translate} from '@akeneo-pim-community/shared';
import {AttributesIllustration, Button, Placeholder, useBooleanState} from 'akeneo-design-system';
import {AddTemplateAttributeModal} from '../AddTemplateAttributeModal';
import styled from 'styled-components';
import {LoadAttributeSetModal} from './LoadAttributeSetModal';
import {useTrackUsageOfLoadPredefinedAttributes} from '../../../hooks/useTrackUsageOfLoadPredefinedAttributes';

interface Props {
  templateId: string;
}

export const InitializeTemplateChoice = ({templateId}: Props) => {
  const trackUsageOfLoadPredefinedAttributes = useTrackUsageOfLoadPredefinedAttributes(templateId);

  const [isAddTemplateAttributeModalOpen, openAddTemplateAttributeModal, closeAddTemplateAttributeModal] =
    useBooleanState(false);

  const [isLoadAttributeSetModalOpen, openLoadAttributeSetModalOpen, closeLoadAttributeSetModalOpen] =
    useBooleanState(false);

  return (
    <Placeholder
      title={translate('akeneo.category.template.initialize.title')}
      illustration={<AttributesIllustration />}
      size="large"
    >
      <TextContainer>
        {translate('akeneo.category.template.initialize.message')}
        <br />
        {translate('akeneo.category.template.initialize.choice_message')}
      </TextContainer>

      <ButtonContainer>
        <Button level="tertiary" ghost onClick={openLoadAttributeSetModalOpen}>
          {translate('akeneo.category.template.initialize.button.load')}
        </Button>
        {isLoadAttributeSetModalOpen && (
          <LoadAttributeSetModal
            templateId={templateId}
            onClose={closeLoadAttributeSetModalOpen}
            onSuccess={() => trackUsageOfLoadPredefinedAttributes('load_predefined_attributes')}
          />
        )}

        <Button level="primary" onClick={openAddTemplateAttributeModal}>
          {translate('akeneo.category.template.initialize.button.create')}
        </Button>
        {isAddTemplateAttributeModalOpen && (
          <AddTemplateAttributeModal
            templateId={templateId}
            onClose={closeAddTemplateAttributeModal}
            onSuccess={() => trackUsageOfLoadPredefinedAttributes('create_first_attribute')}
          />
        )}
      </ButtonContainer>
    </Placeholder>
  );
};

const TextContainer = styled.div`
  text-align: center;
`;

const ButtonContainer = styled.div`
  display: flex;
  gap: 10px;
`;
