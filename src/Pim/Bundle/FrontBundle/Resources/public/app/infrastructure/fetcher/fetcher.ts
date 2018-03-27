export default interface Fetcher<Entity> {
  fetch: (identifier: string) => Promise<Entity>;
};
