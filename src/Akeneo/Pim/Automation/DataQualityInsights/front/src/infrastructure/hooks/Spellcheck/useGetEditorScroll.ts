import {useEffect, useState} from "react";
import {EditorElement} from "../../../domain";

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

  return {
    editorScrollTop,
    editorScrollLeft
  };
};

export default useGetEditorScroll;
