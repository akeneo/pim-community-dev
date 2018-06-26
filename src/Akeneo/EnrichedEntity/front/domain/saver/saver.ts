export default interface Saver<Entity> {
  save: (entity: Entity) => Promise<void>;
}
