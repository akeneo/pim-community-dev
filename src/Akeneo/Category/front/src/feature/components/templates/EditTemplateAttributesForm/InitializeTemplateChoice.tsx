import {translate} from '@akeneo-pim-community/shared';
import {AttributesIllustration, Button, Placeholder, useBooleanState} from 'akeneo-design-system';
import {AddTemplateAttributeModal} from '../AddTemplateAttributeModal';
import styled from 'styled-components';
import {LoadAttributeSetModal} from './LoadAttributeSetModel';

interface Props {
  templateId: string;
}

export const InitializeTemplateChoice = ({templateId}: Props) => {
  const [isAddTemplateAttributeModalOpen, openAddTemplateAttributeModal, closeAddTemplateAttributeModal] =
    useBooleanState(false);

  const [isLoadAttributeSetModalIpen, openLoadAttributeSetModalIpen, closeLoadAttributeSetModalIpen] =
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
        <Button level="tertiary" ghost onClick={openLoadAttributeSetModalIpen}>
          {translate('akeneo.category.template.initialize.button.load')}
        </Button>
        {isLoadAttributeSetModalIpen && (
          <LoadAttributeSetModal templateId={templateId} onClose={closeLoadAttributeSetModalIpen} />
        )}

        <Button level="primary" onClick={openAddTemplateAttributeModal}>
          {translate('akeneo.category.template.initialize.button.create')}
        </Button>
        {isAddTemplateAttributeModalOpen && (
          <AddTemplateAttributeModal templateId={templateId} onClose={closeAddTemplateAttributeModal} />
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
