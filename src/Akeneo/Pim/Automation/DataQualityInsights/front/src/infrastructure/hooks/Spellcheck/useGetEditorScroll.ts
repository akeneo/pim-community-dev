import {useEffect, useState} from "react";
import {EditorElement} from "../../../domain";
import {isTextInput} from "../../../domain/Spellcheck/EditorElement";

const useGetEditorScroll = (editor: EditorElement) => {
  const [editorScrollTop, setEditorScrollTop] = useState<number>(0);
  const [editorScrollLeft, setEditorScrollLeft] = useState<number>(0);

  useEffect(() => {
    setEditorScrollTop(editor.scrollTop);
    setEditorScrollLeft(editor.scrollLeft);
  }, [editor.id]);

  useEffect(() => {
    let lastScrollTop = 0;
    let lastScrollLeft = 0;
    let ticking = false;

    const handleScroll = () => {
      lastScrollTop = editor.scrollTop;
      lastScrollLeft = editor.scrollLeft;

      if (!ticking) {
        window.requestAnimationFrame(function() {
          setEditorScrollTop(lastScrollTop);
          setEditorScrollLeft(lastScrollLeft);
          ticking = false;
        });
        ticking = true;
      }
    };

    editor.addEventListener("scroll", handleScroll, true);

    return () => {
      ticking = true;
      editor.removeEventListener("scroll", handleScroll);
    };
  }, [editor.id]);


  useEffect(() => {
    let ticking = false;

    const handleKeyDown = (event: KeyboardEvent) => {
      if (!ticking) {
        window.requestAnimationFrame(function () {
          const scrollLeft = editor.scrollLeft;
          if (
              event.key === "ArrowLeft" || event.key === "ArrowRight" ||
              event.key === "ArrowUp" || event.key === "ArrowDown" ||
              event.key === "Home" || event.key === "End"
          ) {
            setEditorScrollLeft(scrollLeft);
          }
          ticking = false;
        });
      }
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
      if (isTextInput(editor)) {
        editor.removeEventListener('keydown', handleKeyDown as EventListener);
        editor.removeEventListener('blur', handleBlur);
      }
    };
  }, [editor.id]);

  return {
    editorScrollTop,
    editorScrollLeft
  };
};

export default useGetEditorScroll;
