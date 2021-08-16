import React from 'react';
import {Table} from 'akeneo-design-system';
import {getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {Source} from '../../../models';
import {useAssociationType, useAttribute} from '../../../hooks';

type SourceRowProps = {
  source: Source;
};

const AttributeSourceRow = ({source}: SourceRowProps) => {
  const [, attribute] = useAttribute(source.code);
  const catalogLocale = useUserContext().get('catalogLocale');

  if (null === attribute) return null;

  return (
    <Table.Row>
      <Table.Cell colSpan={2}>{getLabel(attribute.labels, catalogLocale, attribute.code)}</Table.Cell>
    </Table.Row>
  );
};

const PropertySourceRow = ({source}: SourceRowProps) => {
  const translate = useTranslate();

  return (
    <Table.Row>
      <Table.Cell colSpan={2}>{translate(`pim_common.${source.code}`)}</Table.Cell>
    </Table.Row>
  );
};

const AssociationTypeSourceRow = ({source}: SourceRowProps) => {
  const [, associationType] = useAssociationType(source.code);
  const catalogLocale = useUserContext().get('catalogLocale');

  if (null === associationType) return null;

  return (
    <Table.Row>
      <Table.Cell colSpan={2}>{getLabel(associationType.labels, catalogLocale, associationType.code)}</Table.Cell>
    </Table.Row>
  );
};

export {AssociationTypeSourceRow, AttributeSourceRow, PropertySourceRow};
