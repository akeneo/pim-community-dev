import React from 'react';
import {Button, Modal, Tile, Tiles} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {TEMPLATES} from '../models';
import styled from 'styled-components';

const ModalContent = styled.div`
  margin-top: 30px;
  width: 675px;
  max-height: calc(100vh - 120px);
  overflow-x: hidden;
  overflow-y: auto;
`;

type AttributeType = string;

type AttributeData = {
  attribute_type?: AttributeType;
} & {[key: string]: any};

export type CreateAttributeButtonStepProps = {
  onClose: () => void;
  onStepConfirm: (data: AttributeData) => void;
  initialData?: AttributeData;
  onBack?: () => void;
};

const SelectTemplate: React.FC<CreateAttributeButtonStepProps> = ({onStepConfirm, onClose, initialData, onBack}) => {
  const translate = useTranslate();

  React.useEffect(() => {
    if (initialData?.attribute_type !== 'pim_catalog_table') {
      onStepConfirm({});
    }
  }, []);

  const handleBack = () => {
    onBack?.();
  };

  return (
    <Modal closeTitle={translate('pim_common.close')} onClose={onClose}>
      <Modal.TopLeftButtons>
        <Button level={'tertiary'} onClick={handleBack}>
          {translate('pim_common.previous')}
        </Button>
      </Modal.TopLeftButtons>
      <Modal.SectionTitle color='brand'>
        {translate('pim_enrich.entity.attribute.module.create.button')}
      </Modal.SectionTitle>
      <Modal.Title>{translate('pim_table_attribute.templates.choose_template')}</Modal.Title>
      {translate('pim_table_attribute.templates.choose_template_subtitle')}
      <ModalContent>
        <Tiles size='big'>
          {TEMPLATES.map(template => {
            const Icon = template.icon;
            return (
              <Tile
                onClick={() => onStepConfirm({template: template.code})}
                key={template.code}
                icon={<Icon />}
                title={translate(`pim_table_attribute.templates.${template.code}`)}
              >
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
