import React from 'react';
import {Item} from '../Item/Item';

type Props = {
  /**
   * Children are SubNavigation.Item
   */
  children?: React.ReactElement<typeof Item> | React.ReactElement<typeof Item>[];
};

const Section: React.FC<Props> = ({children}) => {
  return <>{children}</>;
};

export {Section};
