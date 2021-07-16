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
          - chown -R 999:999 /data/var/lib/mysql;
            echo done;
        env:
          - { name: SOURCE_PFID, value: "${SOURCE_PFID}" }
        volumeMounts:
          - name: flex-hd
            mountPath: /data
      restartPolicy: Never
      volumes:
      - name: flex-hd
        persistentVolumeClaim:
          claimName: pvc-mysql-mover
  backoffLimit: 0
---
apiVersion: v1
kind: PersistentVolume
metadata:
  name: pv-mysql-mover
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
    name: pvc-mysql-mover
    namespace: "${NAMESPACE}"
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
  storageClassName: ssd-retain
