import {useRef, useState, useEffect} from 'react';
import * as React from 'react';

import styled from 'styled-components';
import {View} from 'backbone';
import {useViewBuilder} from '../hooks';

type Props = {
  viewName: string;
  className: string;
};

const StyledPimView = styled.div<{rendered: boolean}>`
  visibility: ${({rendered}) => (rendered ? 'visible' : 'hidden')};
  opacity: ${({rendered}) => (rendered ? '1' : '0')};
  transition: opacity 0.5s linear;
`;

const PimView = ({viewName, className}: Props) => {
  const el = useRef<HTMLDivElement>(null);
  const [view, setView] = useState<View | null>(null);

  const viewBuilder = useViewBuilder();
  useEffect(() => {
    if (!viewBuilder) {
      return;
    }
    viewBuilder.build(viewName).then((view: View) => {
      // @ts-ignore
      view.setElement(el.current).render();
      setView(view);
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
