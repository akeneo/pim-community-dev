import React, {useRef, useState, useEffect} from 'react';
import {useIsMounted, useViewBuilder, View} from '@akeneo-pim-community/shared';

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
      <div ref={el} className={className} />
    </div>
  );
};

export type {View};

export {HistoryPimView};
