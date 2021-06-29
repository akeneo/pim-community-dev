import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import UserGroups from '../../static/illustrations/UserGroups.svg';

const UserGroupsIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={UserGroups} />
  </svg>
);

export {UserGroupsIllustration};
