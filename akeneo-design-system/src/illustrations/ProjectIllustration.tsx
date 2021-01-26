import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import Project from '../../static/illustrations/Project.svg';

const ProjectIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={Project} />
  </svg>
);

export {ProjectIllustration};
