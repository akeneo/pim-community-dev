import React from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useAttributeContext} from '../contexts';
import {BooleanInput, Field} from 'akeneo-design-system';
import styled from 'styled-components';

type AutoOptionSortingProps = {
  readOnly: boolean;
};

const AutoOptionSorting = ({readOnly}: AutoOptionSortingProps) => {
  const translate = useTranslate();
  const attributeContext = useAttributeContext();

  return (
    <FieldContainer role="toggle-sort-attribute-option">
      <Field label={translate('pim_enrich.entity.attribute.property.auto_option_sorting')}>
        <BooleanInput
          value={attributeContext.autoSortOptions ?? false}
          readOnly={readOnly}
          yesLabel={translate('pim_common.yes')}
          noLabel={translate('pim_common.no')}
          onChange={() => attributeContext.toggleAutoSortOptions()}
        />
      </Field>
    </FieldContainer>
  );
};

const FieldContainer = styled.div`
  margin-top: 20px;
`;

export default AutoOptionSorting;
