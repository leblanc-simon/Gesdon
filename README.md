PROJET
======

Application de gestion des dons et des reçus fiscaux.

Installation
------------

* Clonez le dépot
* Installer les dépendances avec [Composer](http://getcomposer.org/)
* Copier les fichiers de configuration
* Générer les fichiers de classes avec Propel
* Générer le fichier de configuration avec Propel
* Générer la base de données

```bash
git clone [gesdon repository]
cd Gesdon
composer install
cd config
./propel-gen om
./propel-gen convert-conf
```

