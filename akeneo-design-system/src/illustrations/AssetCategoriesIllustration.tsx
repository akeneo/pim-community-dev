import React from 'react';
import {IllustrationProps} from './IllustrationProps';
import AssetCategories from '../../static/illustrations/AssetCategories.svg';

const AssetCategoriesIllustration = ({title, size = 256, ...props}: IllustrationProps) => (
  <svg width={size} height={size} viewBox="0 0 256 256" {...props}>
    {title && <title>{title}</title>}
    <image href={AssetCategories} />
  </svg>
);

export {AssetCategoriesIllustration};
