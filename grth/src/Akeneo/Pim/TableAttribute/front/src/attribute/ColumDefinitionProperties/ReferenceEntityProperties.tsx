import {Field} from 'akeneo-design-system';
import React from 'react';
import {ReferenceEntityColumnDefinition} from '../../models';
import {ColumnProperties} from './index';
import {ReferenceEntitySelector} from '../ReferenceEntitySelector';
import {useTranslate} from '@akeneo-pim-community/shared';

const ReferenceEntityProperties: ColumnProperties = ({selectedColumn}) => {
  const translate = useTranslate();
  const referenceEntityColumn = selectedColumn as ReferenceEntityColumnDefinition;

  return (
    <Field label={translate('pim_table_attribute.form.attribute.reference_entity')}>
      <ReferenceEntitySelector readOnly value={referenceEntityColumn.reference_entity_identifier} />
    </Field>
  );
};

export default ReferenceEntityProperties;
