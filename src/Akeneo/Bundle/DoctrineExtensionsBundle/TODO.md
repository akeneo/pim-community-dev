# MUST HAVE
 - [ ] specs
 - [x] renommer les services pim_catalog présent dans le bundles
 - [x] mettre des alias sur les anciens services dans pim_catalog
 - [ ] faire en sorte que le mapping des interfaces Mongo fonctionne => nettoyer le mapping des interfaces pr faire en sorte que :
    - [ ] les entités ORM soient mappées seulement en ORM
    - [ ] les entités Mongo soient mappées seulement en MongoDBODM
 - [x] renommer AkeneoDoctrineHybridSupportBundle en AkeneoDoctrineExtensionBundle
 
# NICE TO HAVE
 - [ ] déplacer les specs
 - [ ] faire en sorte qu'un fichier orm.yml ou mongodb.yml ne fasse pas planter l'appli
 - [ ] nettoyer les classes AkeneoDoctrineHybridSupportExtension et AkeneoDoctrineHybridSupportBundle afin de faciliter l'intégration
 - [ ] faire en sorte que storage_driver/doctrine mongodb-odm.yml ou orm.yml soit toujours chargé pr tous les bundles (à travers DoctrineHybridSupportExtension)
 - [ ] nettoyer la constante AkeneoDoctrineHybridSupportExtension::DOCTRINE_MONGODB qui ne sert à rien
 - [ ] ajouter un listener identique à Sylius pr le mapping des relations
 - [ ] faire en sorte de définir le mapping des interfaces/entités par la conf normale de Doctrine
 - [ ] changer le storage doctrine-mongodb par hybrid   
