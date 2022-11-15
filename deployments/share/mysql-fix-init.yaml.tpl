apiVersion: batch/v1
kind: Job
metadata:
  name: mysql-fix-init
spec:
  template:
    spec:
      containers:
      - name: mysql-fix-init
        image: mysql:8.0.30
        resources:
          requests:
            memory: 3072Mi
            cpu: 1000m
        command: ["/bin/sh", "-c"]
        args:
          - mysqld --datadir=/var/lib/mysql --skip-grant-tables --user=mysql & sleep 30;
            echo sleep finish;
            mysql -e "FLUSH PRIVILEGES;ALTER USER 'root'@'localhost' IDENTIFIED BY '${MYSQL_ROOT_PASSWORD}';UPDATE mysql.user SET authentication_string = CONCAT('*', UPPER(SHA1(UNHEX(SHA1('${MYSQL_USER_PASSWORD}'))))) , Host='%' WHERE User = 'akeneo_pim';FLUSH PRIVILEGES;GRANT ALL ON akeneo_pim.* TO 'akeneo_pim'@'%'; FLUSH TABLES;";
            echo done;
        env:
          - name: SOURCE_PFID
            value: "${SOURCE_PFID}"
          - name: MYSQL_ROOT_PASSWORD
            value: "${MYSQL_ROOT_PASSWORD}"
          - name: MYSQL_USER_PASSWORD
            value: "${MYSQL_USER_PASSWORD}"
        ports:
          - name: mysql
            containerPort: 3306
        volumeMounts:
          - name: mysql-hd
            mountPath: /var/lib/mysql
      restartPolicy: Never
      volumes:
      - name: mysql-hd
        persistentVolumeClaim:
          claimName: pvc-mysql-fix-init
  backoffLimit: 0
---
apiVersion: v1
kind: PersistentVolume
metadata:
  name: "pv-${NAMESPACE}-mysql-fix-init"
spec:
  accessModes:
  - ReadWriteOnce
  capacity:
    storage: 1Gi
  gcePersistentDisk:
    pdName: ${MYSQL_DISK_NAME}
  persistentVolumeReclaimPolicy: Retain
  storageClassName: ssd-retain
  claimRef:
    apiVersion: v1
    kind: PersistentVolumeClaim
    name: pvc-mysql-fix-init
    namespace: "${NAMESPACE}"
---
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
  name: pvc-mysql-fix-init
spec:
  accessModes:
  - ReadWriteOnce
  resources:
    requests:
      storage: 1Gi
  storageClassName: ssd-retain
