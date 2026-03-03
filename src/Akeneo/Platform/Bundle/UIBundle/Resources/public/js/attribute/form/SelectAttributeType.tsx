import React from 'react';
import {AddAttributeIcon, getColor, IconProps, Link, Modal, Tile, Tiles, Tooltip} from 'akeneo-design-system';
import {useFeatureFlags, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import * as icons from 'akeneo-design-system/lib/icons';
import styled from 'styled-components';
import {CreateAttributeButtonStepProps} from './CreateAttributeButtonApp';
import {useGetIdentifierAttributesCount} from './hooks';
import {TooltipContent, TooltipHeader} from './styles';

const DecoratedLink = styled(Link)`
  color: ${getColor('blue', 120)};
`;

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
  children?: React.ReactNode;
};

//TODO RAC-1225: Remove this function
const isReferenceDataAttributeType = (attributeType: AttributeType): boolean =>
  ['pim_reference_data_simpleselect', 'pim_reference_data_multiselect'].includes(attributeType);

const SelectAttributeType: React.FC<SelectAttributeTypeModalProps> = ({iconsMap, onStepConfirm, onClose, children}) => {
  const translate = useTranslate();
  const Router = useRouter();
  const featureFlags = useFeatureFlags();
  const {count: identifierAttributesCount} = useGetIdentifierAttributesCount();

  const [attributeTypes, setAttributeTypes] = React.useState<AttributeType[] | undefined>();
  const castIcons = icons as {[component: string]: React.FC<IconProps>};

  React.useEffect(() => {
    fetch(Router.generate('pim_enrich_attribute_type_index')).then(response => {
      response.json().then(attributeTypes => {
        let newAttributeTypes = Object.keys(attributeTypes);

        //TODO RAC-1225: Remove this condition
        if (!featureFlags.isEnabled('reference_data')) {
          newAttributeTypes = newAttributeTypes.filter(attributeType => !isReferenceDataAttributeType(attributeType));
        }

        const sortedAttributeTypes = newAttributeTypes.sort((a, b) => {
          return translate(`pim_enrich.entity.attribute.property.type.${a}`).localeCompare(
            translate(`pim_enrich.entity.attribute.property.type.${b}`)
          );
        });

        setAttributeTypes(sortedAttributeTypes);
      });
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
            const isIdentifierLimitReached =
              attributeType === 'pim_catalog_identifier' && identifierAttributesCount >= 10;
            return (
              <Tile
                onClick={() => onStepConfirm({attribute_type: attributeType})}
                key={attributeType}
                icon={<Icon />}
                title={translate(`pim_enrich.entity.attribute.property.type.${attributeType}`)}
                disabled={isIdentifierLimitReached}
              >
                {isIdentifierLimitReached && (
                  <Tooltip direction="top" width={350} iconSize={16}>
                    <TooltipContent>
                      <TooltipHeader>
                        {translate('pim_enrich.entity.attribute.property.identifier_limit_reached_title')}
                      </TooltipHeader>
                      <p>{translate('pim_enrich.entity.attribute.property.identifier_limit_reached')}</p>
                      <DecoratedLink href="https://help.akeneo.com/serenity-build-your-catalog/33-serenity-manage-your-product-identifiers">
                        {translate('pim_enrich.entity.attribute.property.identifier_limit_reached_url')}
                      </DecoratedLink>
                    </TooltipContent>
                  </Tooltip>
                )}
                {translate(`pim_enrich.entity.attribute.property.type.${attributeType}`)}
              </Tile>
            );
          })}
        </Tiles>
      </ModalContent>
      {children}
    </Modal>
  );
};

export default SelectAttributeType;
