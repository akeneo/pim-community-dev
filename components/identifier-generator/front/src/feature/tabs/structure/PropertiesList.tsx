import React from 'react';
import {PropertyLine} from './PropertyLine';
import {Table} from 'akeneo-design-system';
import {Styled} from '../../components/Styled';
import {StructureWithIdentifiers} from '../StructureTab';

type PropertiesListProps = {
  structure: StructureWithIdentifiers;
  onChange: (id: string) => void;
};

const PropertiesList: React.FC<PropertiesListProps> = ({structure, onChange}) => {
  return (
    // eslint-disable-next-line @typescript-eslint/no-empty-function
    <Table isDragAndDroppable={true} onReorder={/* istanbul ignore next */ () => {}}>
      <Table.Body>
        {structure.map(item => (
          <Table.Row key={item.id} onClick={() => onChange(item.id)}>
            <Styled.TitleCell>
              <PropertyLine property={item} />
            </Styled.TitleCell>
          </Table.Row>
        ))}
      </Table.Body>
    </Table>
  );
};

export {PropertiesList};
