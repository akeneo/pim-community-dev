# UPGRADE FROM 1.5 to 1.6

```
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\SecurityBundle\\Model/PimEnterprise\\Component\\Security\\Model/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Bundle\\SecurityBundle\\Entity\\Repository\\AccessRepositoryInterface/PimEnterprise\\Component\\Security\\Repository\\AccessRepositoryInterface/g'
```
