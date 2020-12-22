import React from 'react';
import styled, {css} from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

type Props = {
  isActivated: boolean;
};

const activatedMixin = css`
  color: #3d6b45;
  border: 1px #67b373 solid;
`;

const disabledMixin = css`
  color: #7f392f;
  border: 1px #d4604f solid;
`;

const Badge = styled.div<Props>`
  border-radius: 2px;
  padding: 0 6px;
  display: inline-block;
  line-height: 18px;
  text-transform: uppercase;
  font-size: 11px;
  font-weight: normal;
  background-color: ${({theme}) => theme.color.white};

  ${props => (props.isActivated ? activatedMixin : disabledMixin)}
`;

const StatusBadge = ({isActivated}: Props) => {
  const translate = useTranslate();

  return (
    <Badge isActivated={isActivated}>
      {translate(`akeneo_data_quality_insights.attribute_group.${isActivated ? 'activated' : 'disabled'}`)}
    </Badge>
  );
};

export {StatusBadge};
