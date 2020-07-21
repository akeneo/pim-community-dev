export default interface applySpellingSuggestionInterface {
  (element: HTMLElement, suggestion: string, content: string, start: number, end: number): void;
}
