import {Badge, Collapse, List, useBooleanState} from 'akeneo-design-system';
import React from 'react';

type PossibleValuesProps = {
  label: string;
  values: {code: string; label: string}[];
};

const PossibleValues = ({label, values}: PossibleValuesProps) => {
  const [isOpen, open, close] = useBooleanState(false);

  return (
    <Collapse
      isOpen={isOpen}
      onCollapse={isOpen => (isOpen ? open() : close())}
      collapseButtonLabel="Collapse"
      label={
        <>
          {label} <Badge level="secondary">{values.length}</Badge>
        </>
      }
    >
      <List>
        {values.map(value => (
          <List.Row key={value.code}>
            <List.Cell width="auto">{value.code}</List.Cell>
            <List.Cell width="auto">{value.label !== value.code ? ` (${value.label})` : ''}</List.Cell>
          </List.Row>
        ))}
      </List>
    </Collapse>
  );
};

export {PossibleValues};
