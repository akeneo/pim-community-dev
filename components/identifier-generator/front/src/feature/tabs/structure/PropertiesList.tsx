import React, {useMemo, useState} from 'react';
import {Button, Pill, Table} from 'akeneo-design-system';
import {Styled} from '../../components/Styled';
import {PropertyId, StructureWithIdentifiers} from '../StructureTab';
import {PROPERTY_NAMES} from '../../models';
import {AutoNumberLine, FamilyCodeLine, FreeTextLine, AttributePropertyLine} from './line';
import {useTranslate} from '@akeneo-pim-community/shared';
import {SimpleDeleteModal} from '../../pages';
import {Violation} from '../../validators';
import {useIdentifierGeneratorAclContext} from '../../context';

type PropertiesListProps = {
  structure: StructureWithIdentifiers;
  onSelect: (id: PropertyId) => void;
  selectedId?: PropertyId;
  onReorder: (indices: number[]) => void;
  onDelete: (id: PropertyId) => void;
  validationErrors: Violation[];
};

const PropertiesList: React.FC<PropertiesListProps> = ({
  structure,
  onSelect,
  selectedId,
  onReorder,
  onDelete,
  validationErrors,
}) => {
  const translate = useTranslate();
  const identifierGeneratorAclContext = useIdentifierGeneratorAclContext();
  const [propertyIdToDelete, setPropertyIdToDelete] = useState<PropertyId | undefined>();
  const structureWithErrors = useMemo(
    () =>
      structure.map((property, i) => ({
        ...property,
        errorMessage: validationErrors?.find(({path}) => path?.includes(`structure[${i}]`))?.message,
      })),
    [structure, validationErrors]
  );

  const openModal = (propertyId: PropertyId) => () => {
    setPropertyIdToDelete(propertyId);
  };

  const closeModal = () => {
    setPropertyIdToDelete(undefined);
  };

  const handleDelete = () => {
    if (propertyIdToDelete) {
      onDelete(propertyIdToDelete);
      setPropertyIdToDelete(undefined);
    }
  };

  return (
    <>
      <Table
        isDragAndDroppable={identifierGeneratorAclContext.isManageIdentifierGeneratorAclGranted}
        onReorder={onReorder}
      >
        <Table.Body>
          {structureWithErrors.map(property => (
            <Table.Row key={property.id} onClick={() => onSelect(property.id)} isSelected={property.id === selectedId}>
              <Styled.TitleCell withWidth={false}>
                {property.type === PROPERTY_NAMES.FREE_TEXT && <FreeTextLine freeTextProperty={property} />}
                {property.type === PROPERTY_NAMES.AUTO_NUMBER && <AutoNumberLine property={property} />}
                {property.type === PROPERTY_NAMES.FAMILY && <FamilyCodeLine />}
                {(property.type === PROPERTY_NAMES.SIMPLE_SELECT || property.type === PROPERTY_NAMES.REF_ENTITY) && (
                  <AttributePropertyLine attributeCode={property.attributeCode} />
                )}
                {property.errorMessage && (
                  <Styled.ErrorContainer>
                    <Pill level="danger" />
                  </Styled.ErrorContainer>
                )}
              </Styled.TitleCell>
              <Table.ActionCell>
                {identifierGeneratorAclContext.isManageIdentifierGeneratorAclGranted && (
                  <Button onClick={openModal(property.id)} ghost level="danger">
                    {translate('pim_common.delete')}
                  </Button>
                )}
              </Table.ActionCell>
            </Table.Row>
          ))}
        </Table.Body>
      </Table>
      {propertyIdToDelete && <SimpleDeleteModal onClose={closeModal} onDelete={handleDelete} />}
    </>
  );
};

export {PropertiesList};
