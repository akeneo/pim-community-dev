import React from 'react';
import {Table} from 'akeneo-design-system';
import {Styled} from '../../components/Styled';
import {PropertyId, StructureWithIdentifiers} from '../StructureTab';
import {PROPERTY_NAMES} from '../../models';
import {AutoNumberLine, FreeTextLine} from './line';

type PropertiesListProps = {
  structure: StructureWithIdentifiers;
  onSelect: (id: PropertyId) => void;
  selectedId?: PropertyId;
  onChange: (structure: StructureWithIdentifiers) => void;
};

const PropertiesList: React.FC<PropertiesListProps> = ({structure, onSelect, selectedId, onChange}) => {
  const onReorder = (indices: number[]) => {
    const newStructure: StructureWithIdentifiers = [];
    indices.forEach((lineNumber, i) => {
      newStructure[i] = structure[lineNumber];
    });
    onChange(newStructure);
  };

  return (
    <Table isDragAndDroppable={true} onReorder={onReorder}>
      <Table.Body>
        {structure.map(property => (
          <Table.Row key={property.id} onClick={() => onSelect(property.id)} isSelected={property.id === selectedId}>
            <Styled.TitleCell>
              {property.type === PROPERTY_NAMES.FREE_TEXT && <FreeTextLine freeTextProperty={property} />}
              {property.type === PROPERTY_NAMES.AUTO_NUMBER && <AutoNumberLine property={property} />}
            </Styled.TitleCell>
          </Table.Row>
        ))}
      </Table.Body>
    </Table>
  );
};

export {PropertiesList};
