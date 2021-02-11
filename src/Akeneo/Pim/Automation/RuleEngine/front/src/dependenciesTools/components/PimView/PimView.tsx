import React, {useRef, useState, useEffect} from 'react';
import styled from 'styled-components';
import {View} from 'backbone';
import {useApplicationContext} from '../../hooks';

type Props = {
  className: string;
  viewName: string;
};

const StyledPimView = styled.div<{rendered: boolean}>`
  visibility: ${({rendered}): string => (rendered ? 'visible' : 'hidden')};
  opacity: ${({rendered}): string => (rendered ? '1' : '0')};
  transition: opacity 0.5s linear;
`;

export const PimView: React.FunctionComponent<Props> = ({viewName, className}) => {
  const el = useRef<HTMLDivElement>(null);
  const [view, setView] = useState<View | null>(null);

  const {viewBuilder} = useApplicationContext();
  useEffect(() => {
    if (!viewBuilder) {
      throw new Error('[ApplicationContext]: ViewBuilder has not been properly initiated');
    }
    viewBuilder.build(viewName).then((view: View) => {
      if (null !== el.current) {
        view.setElement(el.current).render();
        setView(view);
      }
    });
  }, [viewBuilder, viewName]);

  useEffect(
    () => (): void => {
      view && view.remove();
    },
    [view]
  );

  return <StyledPimView className={className} ref={el} rendered={null !== view} />;
};
