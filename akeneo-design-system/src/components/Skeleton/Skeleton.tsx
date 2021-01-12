import React, {isValidElement, ReactNode} from 'react';

const recursiveMap = (children: ReactNode, callback: (child: ReactNode) => ReactNode): ReactNode =>
  React.Children.map(children, child => {
    if (!isValidElement<{children?: ReactNode}>(child)) {
      return child;
    }

    if (undefined !== child.props.children) {
      child = React.cloneElement(child, {...child.props, children: recursiveMap(child.props.children, callback)});
    }

    return callback(child);
  });

type SkeletonProps = {
  /**
   * Whether to display children as Skeletons or not.
   */
  enabled?: boolean;

  /**
   * Children.
   */
  children?: ReactNode;
};

/**
 * When enabled, this component will return the Skeleton version of its children.
 */
const Skeleton = ({enabled = false, children}: SkeletonProps) => {
  return (
    <>
      {enabled
        ? recursiveMap(children, (child: ReactNode) => {
            if (isValidElement(child) && 'object' === typeof child.type && 'Skeleton' in child.type) {
              return React.createElement(child.type.Skeleton, child.props);
            }

            return child;
          })
        : children}
    </>
  );
};

export {Skeleton};
