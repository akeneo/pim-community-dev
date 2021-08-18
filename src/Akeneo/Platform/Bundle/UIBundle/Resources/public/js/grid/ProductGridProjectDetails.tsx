import React from 'react';
import {Badge} from 'akeneo-design-system';
import styled from 'styled-components';

type Props = {
  projectDetails: ProjectDetails;
};

type ProjectDetails = {
  dueDateLabel: string;
  dueDate: string;
  completionRatio: number;
};

const getLevel = (completionRatio: number) => {
  if (completionRatio === 0) {
    return 'danger';
  } else if (completionRatio === 100) {
    return 'primary';
  } else {
    return 'warning';
  }
};

const ProductGridProjectDetails = ({projectDetails}: Props) => {
  return (
    <BadgeContainer>
      &nbsp;-&nbsp;
      <Badge level={getLevel(projectDetails.completionRatio)}>{projectDetails.completionRatio} %</Badge>
      &nbsp;-&nbsp;{projectDetails.dueDateLabel}: {projectDetails.dueDate}
    </BadgeContainer>
  );
};

const BadgeContainer = styled.div`
  display: flex;
  align-items: center;
`;

export {ProductGridProjectDetails};
