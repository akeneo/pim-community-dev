import React, {useRef, useEffect} from 'react';
import {useIsMounted, useViewBuilder, View} from '@akeneo-pim-community/shared';

type Props = {
  viewName: string;
  className?: string;
  onBuild?: (view: View) => Promise<View | null>;
  version: number;
};

const HistoryPimView = ({viewName, className, onBuild, version = 0}: Props) => {
  const el = useRef<HTMLDivElement>(null);
  const viewRef = useRef<View | null>(null);

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
      .then((view: View | null) => {
        if (view !== null) {
          viewRef.current = view;
          view.setElement(el.current).render();
        }
      });
  }, [viewBuilder, viewName, isMounted, onBuild, version]);

  useEffect(
    () => () => {
      viewRef.current?.remove();
    },
    []
  );

  return (
    <div>
      <div ref={el} className={className} />
    </div>
  );
};

export type {View};

export {HistoryPimView};
