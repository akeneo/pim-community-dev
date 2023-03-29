import React from 'react';
import styled from 'styled-components';
import {Helper, InfoIcon, Link, getColor} from 'akeneo-design-system';
import {useTranslate} from '../../hooks';

const BACKGROUND_COLOR = '#5e63b6';
const CONTENT_COLOR = '#fff';
const LINK_COLOR = 'blue';

const StyledInfoIcon = styled(InfoIcon)`
  color: ${CONTENT_COLOR};
`;

const StyledLink = styled(Link)`
  color: ${getColor(LINK_COLOR, 60)}; !important;

  & {
    :hover {
      color: ${getColor(LINK_COLOR, 80)};
    }

    :focus:not(:active) {
      box-shadow: 0px 0px 0px 2px rgb(53, 87, 119, 0.3);
    }

    :active {
      color: ${getColor(LINK_COLOR, 100)};
    }
  }
`;

const StyledHelper = styled(Helper)`
  width: 100%;
  background-color: ${BACKGROUND_COLOR};
  color: ${CONTENT_COLOR};
`;

const SandboxHelper = () => {
  const translate = useTranslate();

  return (
    <StyledHelper level="info" icon={<StyledInfoIcon />}>
      {translate('pim_system.sandbox.helper.label')}&nbsp;
      <StyledLink href="https://docs.akeneo.com/latest/cloud_edition/serenity_mode/index.html" target="_blank">
        {translate('pim_system.sandbox.helper.link')}
      </StyledLink>
    </StyledHelper>
  );
};

export {SandboxHelper};
