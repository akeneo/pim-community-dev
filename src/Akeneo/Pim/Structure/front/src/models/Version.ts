export type Version<T, I> = {
  id: number;
  author: string;
  resource_id: I;
  snapshot: T;
  changeset: {
    [key: string]: {old: any; new: any};
  };
  context: null;
  version: number;
  logged_at: string;
  pending: boolean;
};
