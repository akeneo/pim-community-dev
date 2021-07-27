import React from 'react';
import {AddAttributeIcon, IconProps, Modal, Tile, Tiles} from 'akeneo-design-system';
import {baseFetcher, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import * as icons from 'akeneo-design-system/lib/icons';
import styled from 'styled-components';
import {CreateAttributeButtonStepProps} from './CreateAttributeButtonApp';

const ModalContent = styled.div`
  margin-top: 30px;
  width: 745px;
  max-height: calc(100vh - 120px);
  overflow-x: hidden;
  overflow-y: auto;
`;

type AttributeType = string;

type SelectAttributeTypeModalProps = CreateAttributeButtonStepProps & {
  iconsMap: {[attributeType: string]: string};
};

const SelectAttributeType: React.FC<SelectAttributeTypeModalProps> = ({iconsMap, onStepConfirm, onClose}) => {
  const translate = useTranslate();
  const Router = useRouter();

  const [attributeTypes, setAttributeTypes] = React.useState<AttributeType[] | undefined>();
  const castIcons = icons as {[component: string]: React.FC<IconProps>};

  React.useEffect(() => {
    baseFetcher(Router.generate('pim_enrich_attribute_type_index')).then(attributeTypes => {
      const newAttributeTypes = Object.keys(attributeTypes);
      const sortedAttributeTypes = newAttributeTypes.sort((a, b) => {
        return translate(`pim_enrich.entity.attribute.property.type.${a}`).localeCompare(
          translate(`pim_enrich.entity.attribute.property.type.${b}`)
        );
      });

      setAttributeTypes(sortedAttributeTypes);
    });
  }, []);

  return (
    <Modal closeTitle={translate('pim_common.close')} onClose={onClose}>
      <Modal.SectionTitle color="brand">
        {translate('pim_enrich.entity.attribute.module.create.button')}
      </Modal.SectionTitle>
      <Modal.Title>{translate('pim_enrich.entity.attribute.property.type.choose')}</Modal.Title>
      <ModalContent>
        <Tiles>
          {(attributeTypes || []).map(attributeType => {
            const component = iconsMap[attributeType] || 'AddAttributeIcon';
            const Icon = castIcons[component] || AddAttributeIcon;
            return (
              <Tile
                onClick={() => onStepConfirm({attribute_type: attributeType})}
                key={attributeType}
                icon={<Icon />}
                title={translate(`pim_enrich.entity.attribute.property.type.${attributeType}`)}
              >
                {translate(`pim_enrich.entity.attribute.property.type.${attributeType}`)}
              </Tile>
            );
          })}
        </Tiles>
      </ModalContent>
    </Modal>
  );
};

export default SelectAttributeType;
