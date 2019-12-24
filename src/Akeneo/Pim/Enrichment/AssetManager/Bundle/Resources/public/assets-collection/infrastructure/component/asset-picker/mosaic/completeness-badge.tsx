import * as React from 'react';
import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {NormalizedCompleteness} from 'akeneoassetmanager/domain/model/asset/completeness';
import {getCompletenessPercentage} from 'akeneoassetmanager/domain/model/asset/list-asset';

type BadgeProps = {
  isComplete: boolean;
};

const Badge = styled.div<BadgeProps>`
  background-color: white;
  border: 1px solid
    ${(props: ThemedProps<BadgeProps>) => (props.isComplete ? props.theme.color.green120 : props.theme.color.yellow100)};
  font-size: ${(props: ThemedProps<BadgeProps>) => props.theme.fontSize.small};
  color: ${(props: ThemedProps<BadgeProps>) =>
    props.isComplete ? props.theme.color.green120 : props.theme.color.yellow100};
  border-radius: 2px;
  padding: 0 10px;
`;

const CompletenessBadge = ({completeness}: {completeness: NormalizedCompleteness}) => {
  const completenessRatio = getCompletenessPercentage(completeness);
  const isComplete = completenessRatio === 100;

  return <Badge isComplete={isComplete}>{completenessRatio + '%'}</Badge>;
};

export default CompletenessBadge;
