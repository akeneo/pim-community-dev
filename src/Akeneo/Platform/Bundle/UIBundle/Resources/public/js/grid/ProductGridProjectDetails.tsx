import React from 'react';
import {Badge} from 'akeneo-design-system';
import styled from "styled-components";

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
    return 'secondary';
  }
};

const ProductGridProjectDetails = ({projectDetails}: Props) => {
  return (
    <div>
      &nbsp;-&nbsp;<BadgeStyled level={getLevel(projectDetails.completionRatio)}>{projectDetails.completionRatio} %</BadgeStyled>
      &nbsp;-&nbsp;{projectDetails.dueDateLabel}: {projectDetails.dueDate}
    </div>
  );
};

const BadgeStyled = styled(Badge)`
  line-height: 18px;
`;

export {ProductGridProjectDetails};
