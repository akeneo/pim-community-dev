import React, {isValidElement, PropsWithChildren, ReactElement, ReactNode} from 'react';

const recursiveMap = (
  children: ReactNode,
  callback: (child: ReactElement<PropsWithChildren<unknown>>) => ReactNode
): ReactNode =>
  React.Children.map(children, child => {
    if (!isValidElement<PropsWithChildren<unknown>>(child)) {
      return child;
    }

    return callback(
      undefined !== child.props.children
        ? React.cloneElement(child, child.props, recursiveMap(child.props.children, callback))
        : child
    );
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
  const skeleton = 'Skeleton';

  return (
    <>
      {enabled
        ? recursiveMap(children, (child: ReactElement<PropsWithChildren<unknown>>) => {
            if (!('object' === typeof child.type && skeleton in child.type)) {
              return child;
            }

            return React.createElement(child.type[skeleton], child.props);
          })
        : children}
    </>
  );
};

export {Skeleton};
