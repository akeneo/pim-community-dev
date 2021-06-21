import React from 'react';
import {Override} from '../../../../shared';
import {Dropdown as BaseDropdown} from '../../../Dropdown/Dropdown';
import {Link} from '../../../Link/Link';
import {Tag} from '../../../Tags/Tags';
import {Item} from '../Item/Item';

type Props = Override<
  React.HTMLAttributes<HTMLDivElement>,
  {
    children: React.ReactElement<React.ComponentProps<typeof Item>, typeof Item>;
  }
>;

const DropdownItem = React.forwardRef<HTMLDivElement, Props>(
  ({children: item, ...rest}: Props, forwardedRef: React.Ref<HTMLDivElement>) => {
    return (
      <BaseDropdown.Item ref={forwardedRef} {...rest} disabled={item.props.disabled}>
        <Link {...item.props}>
          {React.Children.map(item.props.children, child => {
            if (React.isValidElement(child) && child.type === Tag) {
              return null;
            }

            return child;
          })}
        </Link>
      </BaseDropdown.Item>
    );
  }
);

export {DropdownItem};
