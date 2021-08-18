import React from 'react';
import {Placeholder, Table} from 'akeneo-design-system';
import {getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {Source} from '../../../../models';
import {useAssociationType, useAttribute} from '../../../../hooks';

type SourceRowProps = {
  source: Source;
};

const AttributeSourceRow = ({source, ...rest}: SourceRowProps) => {
  const [isFetching, attribute] = useAttribute(source.code);
  const catalogLocale = useUserContext().get('catalogLocale');

  return (
    <Table.Row {...rest}>
      <Table.Cell colSpan={2}>
        {isFetching ? (
          <Placeholder as="span">{source.code}</Placeholder>
        ) : (
          getLabel(attribute?.labels ?? {}, catalogLocale, source.code)
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
  const [isFetching, associationType] = useAssociationType(source.code);
  const catalogLocale = useUserContext().get('catalogLocale');

  return (
    <Table.Row {...rest}>
      <Table.Cell colSpan={2}>
        {isFetching ? (
          <Placeholder as="span">{source.code}</Placeholder>
        ) : (
          getLabel(associationType?.labels ?? {}, catalogLocale, source.code)
        )}
      </Table.Cell>
    </Table.Row>
  );
};

export {AssociationTypeSourceRow, AttributeSourceRow, PropertySourceRow};
