import React from 'react';
import {Modal, Tiles, Tile} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Template} from '../models/Template';
import styled from 'styled-components';

const ModalContent = styled.div`
  margin-top: 30px;
  width: 675px;
  max-height: calc(100vh - 120px);
  overflow-x: hidden;
  overflow-y: auto;
`;

type SelectTemplateAppProps = {
  onClick: (template: Template) => void;
  onClose: () => void;
  templates: Template[];
};

const SelectTemplateApp: React.FC<SelectTemplateAppProps> = ({onClick, onClose, templates}) => {
  const translate = useTranslate();

  return (
    <Modal closeTitle={translate('pim_common.close')} onClose={onClose}>
      <Modal.SectionTitle color='brand'>
        {translate('pim_enrich.entity.attribute.module.create.button')}
      </Modal.SectionTitle>
      <Modal.Title>{translate('pim_table_attribute.templates.choose_template')}</Modal.Title>
      <ModalContent>
        <Tiles size='big'>
          {templates.map(template => {
            const Icon = template.icon;
            return (
              <Tile
                onClick={() => onClick(template)}
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

export {SelectTemplateApp};
