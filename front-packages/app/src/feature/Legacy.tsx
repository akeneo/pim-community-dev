import React from 'react';
import {useEffect} from 'react';
import {useRef} from 'react';

const loadScript = async (scriptUrl: string): Promise<void> => {
  return new Promise(resolve => {
    const script = document.createElement('script');
    script.onload = function () {
      resolve();
    };
    script.src = scriptUrl;

    document.head.appendChild(script);
  });
};
const loadStyle = async (styleUrl: string): Promise<void> => {
  return new Promise(resolve => {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.type = 'text/css';

    link.onload = function () {
      resolve();
    };
    link.href = styleUrl;

    document.head.appendChild(link);
  });
};

let legacyForm: null | any = null;
const loadLegacy = async (element: HTMLDivElement) => {
  loadStyle('./dist/pim.css');
  await Promise.all([loadScript('./dist/vendor.min.js'), loadScript('./dist/main.min.js')]);

  if (null === legacyForm) {
    legacyForm = (window as any).pimLegacy.build('pim-app');
  }

  legacyForm.then((legacyView: any) => {
    legacyView.setElement(element);
    legacyView.render();
  });
};

const Legacy = () => {
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (null === ref.current) return;
    loadLegacy(ref.current);
  }, []);

  return <div className="app" ref={ref}></div>;
};

export {Legacy};
