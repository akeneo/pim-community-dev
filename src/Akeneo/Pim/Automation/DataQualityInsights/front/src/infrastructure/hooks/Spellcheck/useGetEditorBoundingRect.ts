import {useEffect, useState} from "react";
import {EditorElement} from "../../../domain";

const initalBoundingClientRect: DOMRect = {
  x: 0,
  y: 0,
  width: 0,
  height: 0,
  top: 0,
  bottom: 0,
  left: 0,
  right: 0,
  toJSON: () => {}
};

const useGetEditorBoundingClientRect = (editor: EditorElement) => {
  const [editorBoundingClientRect, setEditorBoundingClientRect] = useState(
    initalBoundingClientRect
  );

  useEffect(() => {
    setEditorBoundingClientRect(editor.getBoundingClientRect());

    return () => {
      setEditorBoundingClientRect(initalBoundingClientRect);
    }
  }, [editor.id]);

  useEffect(() => {
    let lastBoundingClientRect = initalBoundingClientRect;
    let ticking = false;

    const handleResize = () => {
      lastBoundingClientRect = editor.getBoundingClientRect();

      if (!ticking) {
        window.requestAnimationFrame(function() {
          setEditorBoundingClientRect(lastBoundingClientRect);
          ticking = false;
        });
        ticking = true;
      }
    };
    window.addEventListener("resize", handleResize);

    const editorResizeObserver = new ResizeObserver((entries) => {
      for (let entry of entries) {
        if (entry.target === editor) {
          handleResize();
        }
      }
    });

    editorResizeObserver.observe(editor);

    return () => {
      ticking = true;
      window.removeEventListener("resize", handleResize);
      editorResizeObserver.unobserve(editor);
      editorResizeObserver.disconnect();
    };
  }, [editor]);

  useEffect(() => {
    let lastBoundingClientRect = initalBoundingClientRect;
    let ticking = false;

    const handleScroll = () => {
      lastBoundingClientRect = editor.getBoundingClientRect();

      if (!ticking) {
        window.requestAnimationFrame(function() {
          setEditorBoundingClientRect(lastBoundingClientRect);
          ticking = false;
        });
        ticking = true;
      }
    };
    document.addEventListener("scroll", handleScroll, true);

    return () => {
      ticking = true;
      document.removeEventListener("scroll", handleScroll);
    };
  }, [editor]);

  return {
    editorBoundingClientRect,
    setEditorBoundingClientRect
  };
};

export default useGetEditorBoundingClientRect;
