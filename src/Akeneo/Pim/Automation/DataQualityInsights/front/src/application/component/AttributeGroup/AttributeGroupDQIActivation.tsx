import React, {FC} from 'react';
import styled from 'styled-components';
import {HelperMessage} from './HelperMessage';
import {ToggleActivation} from './ToggleActivation';
import {translate} from '@akeneo-pim-community/shared';

type Props = {
  groupCode: string;
};

const ActivationLabel = styled.div`
  margin: 20px 0px 6px 0px;
`;

const AttributeGroupDQIActivation: FC<Props> = ({groupCode}) => {
  return (
    <div>
      <HelperMessage />

      <ActivationLabel>{translate('akeneo_data_quality_insights.attribute_group.activation')}</ActivationLabel>

      <ToggleActivation groupCode={groupCode} />
    </div>
  );
};

export {AttributeGroupDQIActivation};
