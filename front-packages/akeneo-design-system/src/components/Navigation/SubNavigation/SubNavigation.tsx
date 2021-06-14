import React from 'react';
import {SubNavigationPanel} from '../SubNavigationPanel/SubNavigationPanel';
import {Dropdown} from './Dropdown/Dropdown';
import {Item} from './Item/Item';
import {Section} from './Section/Section';

type Props = React.ComponentPropsWithRef<typeof SubNavigationPanel>;

const Component = React.forwardRef(({children, ...rest}: Props, forwardedRef: React.Ref<HTMLDivElement>) => {
  return (
    <SubNavigationPanel ref={forwardedRef} {...rest}>
      <SubNavigationPanel.Collapse>
        <Dropdown>{children}</Dropdown>
      </SubNavigationPanel.Collapse>

      {children}
    </SubNavigationPanel>
  );
});

Component.displayName = 'SubNavigation';

Item.displayName = 'SubNavigation.Item';
Section.displayName = 'SubNavigation.Section';

export const SubNavigation = Object.assign(Component, {Item, Section});
