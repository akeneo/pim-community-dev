apiVersion: batch/v1
kind: Job
metadata:
  name: migration-fixer
  namespace:
spec:
  template:
    spec:
      containers:
      - name: migration-fixer
        image: akeneo/pim-php-base:4.0
        resources:
          requests:
            memory: "256Mi"
            cpu: "0.5"
          limits:
            memory: "512Mi"
            cpu: "1"
        command: ["/bin/sh", "-c", "cd /data/home/akeneo/pim/ && cp vendor/akeneo/pim-community-dev/upgrades/schema/* upgrades/schema/ && cp vendor/akeneo/pim-enterprise-dev/upgrades/schema/* upgrades/schema/ && bin/console doctrine:migrations:version --add --all -q"]
        env:
          - { name: APP_DATABASE_HOST, value: "pim-mysql" }
          - { name: APP_DATABASE_PASSWORD, value: "9vbtChLWrLsRhnKkjozHEqqL" }
          - { name: APP_DATABASE_USER, value: "akeneo_pim" }          
        volumeMounts:
          - name: flex-hd
            mountPath: /data
      restartPolicy: Never
      volumes:
      - name: flex-hd
        persistentVolumeClaim:
          claimName: pvc-migration-fixer
  backoffLimit: 0
---
apiVersion: v1
kind: PersistentVolume
metadata:
  name: pv-migration-fixer-${NAMESPACE}
spec:
  accessModes:
  - ReadWriteOnce
  capacity:
    storage: 1Gi
  gcePersistentDisk:
    pdName: ${DISK_INSTANCE_NAME}
  persistentVolumeReclaimPolicy: Retain
  storageClassName: ssd-retain
  claimRef:
    apiVersion: v1
    kind: PersistentVolumeClaim
    name: pvc-migration-fixer
    namespace: "${NAMESPACE}"
---
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
  name: pvc-migration-fixer
spec:
  accessModes:
  - ReadWriteOnce
  resources:
    requests:
      storage: 1Gi
  storageClassName: ssd-retain
