import React, {useRef, useContext, useState, useEffect} from 'react';
import styled from 'styled-components';
import {LegacyContext} from 'akeneomeasure/context/legacy-context';
import {View} from 'backbone';

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

  const {viewBuilder} = useContext(LegacyContext);
  useEffect(() => {
    if (!viewBuilder) {
      return;
    }
    viewBuilder.build(viewName).then((view: View) => {
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
