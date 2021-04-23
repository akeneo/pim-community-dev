import {useEffect, useLayoutEffect, useState} from 'react';
import {EditorElement} from '../../../application/helper';

const initialBoundingClientRect: DOMRect = {
  x: 0,
  y: 0,
  width: 0,
  height: 0,
  top: 0,
  bottom: 0,
  left: 0,
  right: 0,
  toJSON: () => {},
};

const useGetEditorBoundingClientRect = (editor: EditorElement) => {
  const [editorBoundingClientRect, setEditorBoundingClientRect] = useState(initialBoundingClientRect);

  useEffect(() => {
    setEditorBoundingClientRect(editor.getBoundingClientRect());

    return () => {
      setEditorBoundingClientRect(initialBoundingClientRect);
    };
  }, [editor.id]);

  useEffect(() => {
    let lastBoundingClientRect = initialBoundingClientRect;
    let ticking = false;
    let requestAnimationFrameId: number | null = null;

    const handleResize = () => {
      lastBoundingClientRect = editor.getBoundingClientRect();

      if (!ticking) {
        requestAnimationFrameId = window.requestAnimationFrame(function() {
          setEditorBoundingClientRect(lastBoundingClientRect);
          ticking = false;
        });
        ticking = true;
      }
    };
    window.addEventListener('resize', handleResize);

    const editorResizeObserver = new ResizeObserver((entries: ResizeObserverEntry[]) => {
      for (let entry of entries) {
        if (entry.target === editor) {
          handleResize();
        }
      }
    });

    editorResizeObserver.observe(editor);

    return () => {
      ticking = true;

      if (requestAnimationFrameId !== null) {
        window.cancelAnimationFrame(requestAnimationFrameId);
        requestAnimationFrameId = null;
      }

      window.removeEventListener('resize', handleResize);
      editorResizeObserver.unobserve(editor);
      editorResizeObserver.disconnect();
    };
  }, [editor]);

  useEffect(() => {
    let lastBoundingClientRect = initialBoundingClientRect;
    let ticking = false;
    let requestAnimationFrameId: number | null = null;

    const handleScroll = () => {
      lastBoundingClientRect = editor.getBoundingClientRect();

      if (!ticking) {
        requestAnimationFrameId = window.requestAnimationFrame(function() {
          setEditorBoundingClientRect(lastBoundingClientRect);
          ticking = false;
        });
        ticking = true;
      }
    };
    document.addEventListener('scroll', handleScroll, true);

    return () => {
      ticking = true;

      if (requestAnimationFrameId !== null) {
        window.cancelAnimationFrame(requestAnimationFrameId);
        requestAnimationFrameId = null;
      }

      document.removeEventListener('scroll', handleScroll);
    };
  }, [editor]);

  useLayoutEffect(() => {
    let ticking = false;
    let requestAnimationFrameId: number | null = null;

    const observer = new MutationObserver(() => {
      if (!ticking) {
        requestAnimationFrameId = window.requestAnimationFrame(function() {
          const clientRect = editor.getBoundingClientRect();
          setEditorBoundingClientRect(clientRect);
          ticking = false;
        });
        ticking = true;
      }
    });

    observer.observe(document, {
      childList: true,
      subtree: true,
    });

    return () => {
      observer.disconnect();
      ticking = true;

      if (requestAnimationFrameId !== null) {
        window.cancelAnimationFrame(requestAnimationFrameId);
        requestAnimationFrameId = null;
      }
    };
  }, [editor]);

  return {
    editorBoundingClientRect,
    setEditorBoundingClientRect,
  };
};

export default useGetEditorBoundingClientRect;
