import React from 'react';
import {SkeletonPlaceholder, Table} from 'akeneo-design-system';
import {getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {Source} from '../../../../models';
import {useAssociationType, useAttribute} from '../../../../hooks';
import {isStaticStringSource} from '../../../SourceDetails/Static';

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
          <SkeletonPlaceholder as="span">{source.code}</SkeletonPlaceholder>
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

const StaticSourceRow = ({source, ...rest}: SourceRowProps) => {
  const translate = useTranslate();

  if (!isStaticStringSource(source)) {
    throw new Error('Invalid source type');
  }

  return (
    <Table.Row {...rest}>
      <Table.Cell colSpan={2}>
        {source.value} ({translate(`akeneo.syndication.data_mapping_details.sources.static.${source.code}.title`)})
      </Table.Cell>
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
          <SkeletonPlaceholder as="span">{source.code}</SkeletonPlaceholder>
        ) : (
          getLabel(associationType?.labels ?? {}, catalogLocale, source.code)
        )}
      </Table.Cell>
    </Table.Row>
  );
};

export {AssociationTypeSourceRow, AttributeSourceRow, PropertySourceRow, StaticSourceRow};
