import {useEffect, useLayoutEffect, useState} from 'react';
import {EditorElement, isTextInput} from '../../../application/helper';
import {useMountedState} from '../Common/useMountedState';

const useGetEditorScroll = (editor: EditorElement) => {
  const [editorScrollTop, setEditorScrollTop] = useState<number>(0);
  const [editorScrollLeft, setEditorScrollLeft] = useState<number>(0);
  const {isMounted} = useMountedState();

  useEffect(() => {
    setEditorScrollTop(editor.scrollTop);
    setEditorScrollLeft(editor.scrollLeft);
  }, [editor.id]);

  useEffect(() => {
    let lastScrollTop = 0;
    let lastScrollLeft = 0;
    let ticking = false;
    let requestAnimationFrameId: number | null = null;

    const handleScroll = () => {
      lastScrollTop = editor.scrollTop;
      lastScrollLeft = editor.scrollLeft;

      if (!isMounted() || ticking) {
        return;
      }

      requestAnimationFrameId = window.requestAnimationFrame(() => {
        setEditorScrollTop(lastScrollTop);
        setEditorScrollLeft(lastScrollLeft);
        ticking = false;
      });
      ticking = true;
    };

    editor.addEventListener('scroll', handleScroll, true);

    return () => {
      if (requestAnimationFrameId !== null) {
        window.cancelAnimationFrame(requestAnimationFrameId);
        requestAnimationFrameId = null;
      }

      editor.removeEventListener('scroll', handleScroll);
    };
  }, [editor.id]);

  useEffect(() => {
    let ticking = false;
    let requestAnimationFrameId: number | null = null;

    const handleKeyDown = (event: KeyboardEvent) => {
      if (!isMounted() || ticking) {
        return;
      }

      requestAnimationFrameId = window.requestAnimationFrame(() => {
        const scrollLeft = editor.scrollLeft;
        if (
          event.key === 'ArrowLeft' ||
          event.key === 'ArrowRight' ||
          event.key === 'ArrowUp' ||
          event.key === 'ArrowDown' ||
          event.key === 'Home' ||
          event.key === 'End'
        ) {
          setEditorScrollLeft(scrollLeft);
        }
        ticking = false;
      });
      ticking = true;
    };

    const handleBlur: EventListener = () => {
      setEditorScrollLeft(0);
    };

    if (isTextInput(editor)) {
      editor.addEventListener('keydown', handleKeyDown as EventListener);
      editor.addEventListener('blur', handleBlur);
    }

    return () => {
      ticking = true;

      if (requestAnimationFrameId !== null) {
        window.cancelAnimationFrame(requestAnimationFrameId);
        requestAnimationFrameId = null;
      }

      if (isTextInput(editor)) {
        editor.removeEventListener('keydown', handleKeyDown as EventListener);
        editor.removeEventListener('blur', handleBlur);
      }
    };
  }, [editor.id]);

  useLayoutEffect(() => {
    let ticking = false;
    let buttonPressedInEditor = false;
    let requestAnimationFrameId: number | null = null;

    const handleMouseMove = (event: MouseEvent) => {
      if (!isMounted() || !isTextInput(editor) || ticking) {
        return;
      }

      requestAnimationFrameId = window.requestAnimationFrame(() => {
        const isEditor = event.target === editor;

        if (isEditor && !buttonPressedInEditor) {
          buttonPressedInEditor = event.buttons === 1;
        }

        if (isEditor || buttonPressedInEditor) {
          setEditorScrollLeft(editor.scrollLeft);
        }

        ticking = false;
      });

      ticking = true;
    };

    const handleMouseUp = (event: MouseEvent) => {
      if (!isMounted() || !isTextInput(editor)) {
        return;
      }

      const isEditor = event.target === editor;
      if (isEditor) {
        setEditorScrollLeft(editor.scrollLeft);
      }

      if (buttonPressedInEditor) {
        buttonPressedInEditor = false;
      }
    };

    document.addEventListener('mousemove', handleMouseMove as EventListener, true);
    document.addEventListener('mouseup', handleMouseUp as EventListener, true);

    return () => {
      ticking = true;

      if (requestAnimationFrameId !== null) {
        window.cancelAnimationFrame(requestAnimationFrameId);
        requestAnimationFrameId = null;
      }

      document.removeEventListener('mousemove', handleMouseMove as EventListener);
      document.removeEventListener('mouseup', handleMouseUp as EventListener);
    };
  }, []);

  return {
    editorScrollTop,
    editorScrollLeft,
  };
};

export default useGetEditorScroll;
