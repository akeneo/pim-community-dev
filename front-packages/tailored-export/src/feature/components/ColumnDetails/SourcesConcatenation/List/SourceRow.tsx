import React from 'react';
import {Placeholder, Table} from 'akeneo-design-system';
import {getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {Source} from '../../../../models';
import {useAssociationType, useAttribute} from '../../../../hooks';

type SourceRowProps = {
  source: Source;
};

const AttributeSourceRow = ({source, ...rest}: SourceRowProps) => {
  const [, attribute] = useAttribute(source.code);
  const catalogLocale = useUserContext().get('catalogLocale');

  return (
    <Table.Row {...rest}>
      <Table.Cell colSpan={2}>
        {null === attribute ? (
          <Placeholder as="span">{source.code}</Placeholder>
        ) : (
          getLabel(attribute.labels, catalogLocale, attribute.code)
        )}
      </Table.Cell>
    </Table.Row>
  );
};

const PropertySourceRow = ({source, ...rest}: SourceRowProps) => {
  const translate = useTranslate();

  return (
    <Table.Row {...rest}>
      <Table.Cell colSpan={2}>{translate(`pim_common.${source.code}`)}</Table.Cell>
    </Table.Row>
  );
};

const AssociationTypeSourceRow = ({source, ...rest}: SourceRowProps) => {
  const [, associationType] = useAssociationType(source.code);
  const catalogLocale = useUserContext().get('catalogLocale');

  return (
    <Table.Row {...rest}>
      <Table.Cell colSpan={2}>
        {null === associationType ? (
          <Placeholder as="span">{source.code}</Placeholder>
        ) : (
          getLabel(associationType.labels, catalogLocale, associationType.code)
        )}
      </Table.Cell>
    </Table.Row>
  );
};

export {AssociationTypeSourceRow, AttributeSourceRow, PropertySourceRow};
