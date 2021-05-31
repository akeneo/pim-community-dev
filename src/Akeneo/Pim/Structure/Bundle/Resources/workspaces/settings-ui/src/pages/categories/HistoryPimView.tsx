import React, {useRef, useState, useEffect} from 'react';
import {useIsMounted, useViewBuilder} from '@akeneo-pim-community/shared';

type View = {
  setElement: (element: HTMLElement | null) => View;
  render: () => void;
  remove: () => void;
  setData: (data: any, options?: {silent?: boolean}) => void;
};

type Props = {
  viewName: string;
  className?: string;
  onBuild?: (view: View) => Promise<View>;
};

const HistoryPimView = ({viewName, className, onBuild}: Props) => {
  const el = useRef<HTMLDivElement>(null);
  const [view, setView] = useState<View | null>(null);

  const viewBuilder = useViewBuilder();
  const isMounted = useIsMounted();
  useEffect(() => {
    if (!viewBuilder) {
      return;
    }

    viewBuilder
      .build(viewName)
      .then((view: View) => {
        if (typeof onBuild === 'function') {
          return onBuild(view);
        }
        return view;
      })
      .then((view: View) => {
        if (isMounted()) {
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

  return (
    <div>
      <div ref={el} />
    </div>
  );
};

export type {View};

export {HistoryPimView};
