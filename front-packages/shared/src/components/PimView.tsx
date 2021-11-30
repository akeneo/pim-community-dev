import React, {useRef, useState, useEffect} from 'react';
import styled from 'styled-components';
import {useIsMounted, useViewBuilder} from '../hooks';
import {View} from '../DependenciesProvider.type';

type Props = {
  viewName: string;
  className?: string;
  parent?: any;
  data?: any;
};

const StyledPimView = styled.div<{rendered: boolean}>`
  visibility: ${({rendered}) => (rendered ? 'visible' : 'hidden')};
  opacity: ${({rendered}) => (rendered ? '1' : '0')};
  transition: opacity 0.5s linear;
`;

const PimView = ({viewName, className, parent, data}: Props) => {
  const el = useRef<HTMLDivElement>(null);
  const [view, setView] = useState<View | null>(null);

  const viewBuilder = useViewBuilder();
  const isMounted = useIsMounted();
  useEffect(() => {
    if (!viewBuilder) {
      return;
    }

    viewBuilder.build(viewName).then(view => {
      if (isMounted()) {
        if (parent && data) {
          //@ts-ignore
          view.setParent(parent);
          //@ts-ignore
          view.setData(data);
        }
        view.setElement(el.current).render();
        setView(view);
      }
    });
  }, [viewBuilder, viewName]);

  useEffect(
    () => () => {
      view && view.remove();
    },
    [view]
  );

  return <StyledPimView className={className} ref={el} rendered={null !== view} />;
};

export {PimView};
