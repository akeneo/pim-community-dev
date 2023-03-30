import React from 'react';
import styled from 'styled-components';
import {Helper, InfoIcon, Link, getColor} from 'akeneo-design-system';
import {useFeatureFlags, useTranslate} from '../../hooks';

const HELPER_LINK = 'https://docs.akeneo.com/latest/cloud_edition/serenity_mode/index.html';
const HELPER_BACKGROUND_COLOR = '#5e63b6';

const StyledInfoIcon = styled(InfoIcon)`
  color: ${getColor('white')};
`;

const StyledHelper = styled(Helper)`
  background-color: ${HELPER_BACKGROUND_COLOR};
  color: ${getColor('white')};

  a,
  a:hover {
    color: ${getColor('white')};
  }
`;

const SandboxHelper = () => {
  const translate = useTranslate();
  const {isEnabled} = useFeatureFlags();

  if (!isEnabled('reset_pim')) {
    return null;
  }

  return (
    <StyledHelper level="info" icon={<StyledInfoIcon />}>
      {translate('pim_system.sandbox.helper.label')}&nbsp;
      <Link href={HELPER_LINK} target="_blank">
        {translate('pim_system.sandbox.helper.link')}
      </Link>
    </StyledHelper>
  );
};

export {SandboxHelper};
