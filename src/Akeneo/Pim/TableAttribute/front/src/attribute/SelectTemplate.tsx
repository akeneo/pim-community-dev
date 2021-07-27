import React from 'react';
import {Modal, Tiles, Tile} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {TEMPLATES} from '../models/Template';
import styled from 'styled-components';
import {CreateAttributeButtonStepProps} from 'pim-community-dev/public/bundles/pimui/js/attribute/form/CreateAttributeButtonApp';

const ModalContent = styled.div`
  margin-top: 30px;
  width: 675px;
  max-height: calc(100vh - 120px);
  overflow-x: hidden;
  overflow-y: auto;
`;

const SelectTemplate: React.FC<CreateAttributeButtonStepProps> = ({onStepConfirm, onClose, initialData}) => {
  const translate = useTranslate();

  React.useEffect(() => {
    if (initialData?.attribute_type !== 'pim_catalog_table') {
      onStepConfirm({});
    }
  }, []);

  return (
    <Modal closeTitle={translate('pim_common.close')} onClose={onClose}>
      <Modal.SectionTitle color='brand'>
        {translate('pim_enrich.entity.attribute.module.create.button')}
      </Modal.SectionTitle>
      <Modal.Title>{translate('pim_table_attribute.templates.choose_template')}</Modal.Title>
      <ModalContent>
        <Tiles size='big'>
          {TEMPLATES.map(template => {
            const Icon = template.icon;
            return (
              <Tile
                onClick={() => onStepConfirm({template: template.code})}
                key={template.code}
                icon={<Icon />}
                title={translate(`pim_table_attribute.templates.${template.code}`)}>
                {translate(`pim_table_attribute.templates.${template.code}`)}
              </Tile>
            );
          })}
        </Tiles>
      </ModalContent>
    </Modal>
  );
};

export const view = SelectTemplate;
