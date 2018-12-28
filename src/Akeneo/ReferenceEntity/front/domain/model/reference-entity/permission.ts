import permission from 'web/bundles/akeneoreferenceentity/application/hydrator/permission';

export enum RightLevel {
  None = 'none',
  View = 'view',
  Edit = 'edit',
  Own = 'own',
}

type UserGroupIdentifier = number;
type UserGroupName = string;

export interface NormalizedPermission {
  user_group_identifier: UserGroupIdentifier;
  user_group_name: UserGroupName;
  right_level: RightLevel;
}

export default interface Permission {
  getUserGroupIdentifier: () => UserGroupIdentifier;
  getUserGroupName: () => UserGroupName;
  getRightLevel: () => RightLevel;
  updateRightLevel(rightLevel: RightLevel): Permission;
  normalize: () => NormalizedPermission;
}

class InvalidArgumentError extends Error {}

class PermissionImplementation implements Permission {
  private constructor(
    private userGroupIdentifier: UserGroupIdentifier,
    private userGroupName: UserGroupName,
    private rightLevel: RightLevel
  ) {
    if (!('number' === typeof userGroupIdentifier)) {
      throw new InvalidArgumentError('Permission expect a number as userGroupIdentifier argument');
    }
    if (!('string' === typeof userGroupName)) {
      throw new InvalidArgumentError('Permission expect a string as userGroupName argument');
    }
    if (!('string' === typeof rightLevel)) {
      throw new InvalidArgumentError('Permission expect a string as rightLevel argument');
    }

    Object.freeze(this);
  }

  public static create(
    userGroupIdentifier: UserGroupIdentifier,
    userGroupName: UserGroupName,
    rightLevel: RightLevel
  ): Permission {
    return new PermissionImplementation(userGroupIdentifier, userGroupName, rightLevel);
  }

  public static createFromNormalized(normalizedPermission: NormalizedPermission): Permission {
    return PermissionImplementation.create(
      normalizedPermission.user_group_identifier,
      normalizedPermission.user_group_name,
      normalizedPermission.right_level
    );
  }

  getUserGroupIdentifier() {
    return this.userGroupIdentifier;
  }

  getUserGroupName() {
    return this.userGroupName;
  }

  getRightLevel() {
    return this.rightLevel;
  }

  updateRightLevel(rightLevel: RightLevel): Permission {
    return PermissionImplementation.create(this.userGroupIdentifier, this.userGroupName, rightLevel);
  }

  normalize() {
    return {
      user_group_identifier: this.getUserGroupIdentifier(),
      user_group_name: this.getUserGroupName(),
      right_level: this.getRightLevel(),
    };
  }
}

type NormalizedPermissionCollection = NormalizedPermission[];

export interface PermissionCollection {
  getPermission(userGroupName: UserGroupName): Permission;
  setPermission(userGroupName: UserGroupName, rightLevel: RightLevel): PermissionCollection;
  setAllPermissions(rightLevel: RightLevel): PermissionCollection;
}

class PermissionCollectionImplementation implements PermissionCollection {
  private constructor(private permissions: Permission[]) {
    permissions.forEach((permission: Permission) => {
      if (!(permission instanceof PermissionImplementation)) {
        throw new InvalidArgumentError('PermissionCollection expect only Permission objects as argument');
      }
    });

    Object.freeze(this);
  }

  public static create(permissions: Permission[]): PermissionCollection {
    return new PermissionCollectionImplementation(permissions);
  }

  public static createFromNormalized(
    normalizedPermissionCollection: NormalizedPermissionCollection
  ): PermissionCollection {
    return PermissionCollectionImplementation.create(
      normalizedPermissionCollection.map(PermissionImplementation.createFromNormalized)
    );
  }

  getPermission(userGroupName: UserGroupName) {
    const permission = this.permissions.find(
      (permission: Permission) => permission.getUserGroupName() === userGroupName
    );

    if (undefined === permission) {
      throw new InvalidArgumentError(
        `The permission for group "${userGroupName}" was not found in the permission collection`
      );
    }

    return permission;
  }

  setAllPermissions(rightLevel: RightLevel): PermissionCollection {
    return PermissionCollectionImplementation.create(
      this.permissions.map((permission: Permission) => permission.updateRightLevel(rightLevel))
    );
  }

  setPermission(userGroupName: UserGroupName, rightLevel: RightLevel): PermissionCollection {
    return PermissionCollectionImplementation.create(
      this.permissions.map((permission: Permission) => {
        if (permission.getUserGroupName() === userGroupName) {
          return permission.updateRightLevel(rightLevel);
        }

        return permission;
      })
    );
  }

  normalize() {
    return this.permissions.map((permission: Permission) => permission.normalize());
  }
}

export const createPermissionCollection = PermissionCollectionImplementation.create;
export const denormalizePermissionCollection = PermissionCollectionImplementation.createFromNormalized;
