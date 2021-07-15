import React from 'react';
import {Modal, Tile, Tiles, useBooleanState, Button, AddAttributeIcon, IconProps} from 'akeneo-design-system';
import {baseFetcher, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import * as icons from 'akeneo-design-system/lib/icons';

const ModalContent = styled.div`
  margin-top: 30px;
  width: 745px;
  max-height: calc(100vh - 120px);
  overflow-x: hidden;
  overflow-y: auto;
`;

type AttributeType = string;

type CreateButtonAppProps = {
  buttonTitle: string;
  iconsMap: {[attributeType: string]: string};
  isModalOpen?: boolean;
  onClick: (attributeType: AttributeType) => void;
};

const CreateButtonApp: React.FC<CreateButtonAppProps> = ({buttonTitle, iconsMap, isModalOpen = false, onClick}) => {
  const [isOpen, open, close] = useBooleanState(isModalOpen);
  const [attributeTypes, setAttributeTypes] = React.useState<AttributeType[] | undefined>();
  const translate = useTranslate();
  const Router = useRouter();

  React.useEffect(() => {
    if (isOpen && !attributeTypes) {
      baseFetcher(Router.generate('pim_enrich_attribute_type_index')).then(attributeTypes => {
        const newAttributeTypes = Object.keys(attributeTypes);
        const sortedAttributeTypes = newAttributeTypes.sort((a, b) => {
          return translate(`pim_enrich.entity.attribute.property.type.${a}`).localeCompare(
            translate(`pim_enrich.entity.attribute.property.type.${b}`)
          );
        });

        setAttributeTypes(sortedAttributeTypes);
      });
    }
  }, [isOpen]);

  const handleClick = (attributeType: AttributeType) => {
    close();
    onClick(attributeType);
  };

  const castIcons = icons as {[component: string]: React.FC<IconProps>};

  return (
    <>
      {isOpen && attributeTypes && (
        <Modal closeTitle={translate('pim_common.close')} onClose={close}>
          <Modal.SectionTitle color="brand">
            {translate('pim_enrich.entity.attribute.module.create.button')}
          </Modal.SectionTitle>
          <Modal.Title>{translate('pim_enrich.entity.attribute.property.type.choose')}</Modal.Title>
          <ModalContent>
            <Tiles>
              {attributeTypes.map(attributeType => {
                const component = iconsMap[attributeType] || 'AddAttributeIcon';
                const Icon = castIcons[component] || AddAttributeIcon;
                return (
                  <Tile
                    onClick={() => handleClick(attributeType)}
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
      )}
      <Button id="attribute-create-button" onClick={open}>
        {buttonTitle}
      </Button>
    </>
  );
};

export {CreateButtonApp};
