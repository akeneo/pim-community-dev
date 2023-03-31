import {useTranslate} from '@akeneo-pim-community/shared';
import {DeleteIllustration, Helper, Modal} from "akeneo-design-system";
import styled from "styled-components";

type Props = {
    templateUuid: string;
    attribute: {uuid: string; label: string};
    onClose: () => void;
};

export const DeleteTemplateAttributeModal = ({templateUuid, attribute, onClose}: Props) =>
{
    const translate = useTranslate();

    return (
        <Modal illustration={<DeleteIllustration />} closeTitle={translate('pim_common.close')} onClose={onClose}>
            <Modal.SectionTitle color="brand">
                {translate('akeneo.category.template.deactivate.deactivate_template')}
            </Modal.SectionTitle>
            <Modal.Title>
                {translate('akeneo.category.template.delete_attribute.confirmation_modal.title')}
            </Modal.Title>
            <Content>
                {translate('akeneo.category.template.delete_attribute.confirmation_modal.message', {attribute: attribute.label})}
            </Content>
            <Helper level="error">
                {translate('akeneo.category.template.delete_attribute.confirmation_modal.helper')}
            </Helper>
        </Modal>
    );
}

const Content = styled.div`
  padding-bottom: 20px;
`;
