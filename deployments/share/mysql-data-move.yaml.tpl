apiVersion: batch/v1
kind: Job
metadata:
  name: mysql-data-move
spec:
  template:
    spec:
      containers:
      - name: mysql-data-move
        image: alpine:3
        resources:
          requests:
            memory: "64Mi"
            cpu: "0.5"
          limits:
            memory: "128Mi"
            cpu: "0.7"
        command: ["/bin/sh", "-c"]
        args:
          - rm -rf /data-mysql/*;
            cp -r /data/var/lib/mysql/* /data-mysql;
            chown -R 999:999 /data-mysql;
            echo done;
        env:
          - name: SOURCE_PFID
            value: "${SOURCE_PFID}"
        volumeMounts:
          - name: flex-hd
            mountPath: /data
          - name: mysql-hd
            mountPath: /data-mysql
      restartPolicy: Never
      volumes:
      - name: flex-hd
        persistentVolumeClaim:
          claimName: pvc-flex-mover
      - name: mysql-hd
        persistentVolumeClaim:
          claimName: pvc-mysql-mover
  backoffLimit: 0
---
apiVersion: v1
kind: PersistentVolume
metadata:
  name: "pv-${NAMESPACE}-flex-mover"
spec:
  accessModes:
  - ReadWriteOnce
  capacity:
    storage: 1Gi
  gcePersistentDisk:
    pdName: ${FLEX_DISK_NAME}
  persistentVolumeReclaimPolicy: Retain
  storageClassName: ssd-retain
  claimRef:
    apiVersion: v1
    kind: PersistentVolumeClaim
    name: pvc-flex-mover
    namespace: "${NAMESPACE}"
---
apiVersion: v1
kind: PersistentVolume
metadata:
  name: "pv-${NAMESPACE}-mysql-mover"
spec:
  accessModes:
  - ReadWriteOnce
  capacity:
    storage: 1Gi
  gcePersistentDisk:
    pdName: ${MYSQL_DISK_NAME}
  persistentVolumeReclaimPolicy: Retain
  storageClassName: ssd-retain-csi
  claimRef:
    apiVersion: v1
    kind: PersistentVolumeClaim
    name: pvc-mysql-mover
    namespace: "${NAMESPACE}"
---
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
  name: pvc-flex-mover
spec:
  accessModes:
  - ReadWriteOnce
  resources:
    requests:
      storage: 1Gi
  storageClassName: ssd-retain
---
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
  name: pvc-mysql-mover
spec:
  accessModes:
  - ReadWriteOnce
  resources:
    requests:
      storage: 1Gi
  storageClassName: ssd-retain-csi
