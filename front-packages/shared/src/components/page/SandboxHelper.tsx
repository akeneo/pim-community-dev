import React from 'react';
import styled from 'styled-components';
import {Helper, HelperProps, InfoIcon, getColor} from 'akeneo-design-system';
import {useFeatureFlags, useSystemConfiguration, useTranslate} from '../../hooks';

const HELPER_BACKGROUND_COLOR = '#5e63b6';

const WhiteInfoIcon = styled(InfoIcon)`
  color: ${getColor('white')};
`;

const DarkBlueHelper = styled(Helper)`
  background-color: ${HELPER_BACKGROUND_COLOR};
  color: ${getColor('white')};
  position: sticky;
  top: 0;
  z-index: 20;
`;

const SandboxHelper = (props: Omit<HelperProps, 'children'>) => {
  const translate = useTranslate();
  const {isEnabled} = useFeatureFlags();
  const shouldDisplayBanner = isEnabled('sandbox_banner') && true === useSystemConfiguration().get('sandbox_banner');

  if (!shouldDisplayBanner) {
    return null;
  }

  return (
    <DarkBlueHelper level="info" icon={<WhiteInfoIcon />} {...props}>
      {translate('pim_system.sandbox.helper.text')}
    </DarkBlueHelper>
  );
};

export {SandboxHelper};
