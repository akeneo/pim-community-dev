import React from 'react';
import styled from 'styled-components';
import {Helper, InfoIcon, getColor} from 'akeneo-design-system';
import {useFeatureFlags, useTranslate} from '../../hooks';

const HELPER_BACKGROUND_COLOR = '#5e63b6';

const WhiteInfoIcon = styled(InfoIcon)`
  color: ${getColor('white')};
`;

const DarkBlueHelper = styled(Helper)`
  background-color: ${HELPER_BACKGROUND_COLOR};
  color: ${getColor('white')};
`;

const SandboxHelper = () => {
  const translate = useTranslate();
  const {isEnabled} = useFeatureFlags();

  if (!isEnabled('reset_pim')) {
    return null;
  }

  return (
    <DarkBlueHelper level="info" icon={<WhiteInfoIcon />}>
      {translate('pim_system.sandbox.helper.text')}
    </DarkBlueHelper>
  );
};

export {SandboxHelper};
