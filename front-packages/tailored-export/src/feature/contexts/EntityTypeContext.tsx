import React, {createContext, useContext, ReactNode} from 'react';

type EntityTypeValue = 'product' | 'product_model';

const EntityTypeContext = createContext<EntityTypeValue>('product');

const useEntityType = (): EntityTypeValue => {
  return useContext(EntityTypeContext);
};

type EntityTypeProviderProps = {
  entityType: EntityTypeValue;
  children: ReactNode;
};

const EntityTypeProvider = ({entityType, children}: EntityTypeProviderProps) => {
  return <EntityTypeContext.Provider value={entityType}>{children}</EntityTypeContext.Provider>;
};

export {EntityTypeProvider, useEntityType};

export type {EntityTypeValue};
