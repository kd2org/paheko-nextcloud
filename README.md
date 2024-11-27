# Application Paheko pour NextCloud

![Capture d'écran](https://github.com/user-attachments/assets/dd6c4f8c-ca63-4464-bbf6-5abd48be5640)

Permet d'installer Paheko comme application NextCloud, pour gérer membres et comptabilité.

Testé avec Nextcloud Hub 9 (30.0.2) en 2024. Devrait fonctionner avec toute version de NextCloud à partir de la version 24.

## Installation

1. Télécharger le code
2. Décompresser l'archive dans le répertoire `apps/paheko` de NextCloud.
3. Activer l'app Paheko dans la configuration de NextCloud.
4. Se rendre sur le menu de l'application Paheko dans NextCloud.

## Comment ça fonctionne ?

Cette app ne fait que télécharger et installer Paheko dans un sous-répertoire de NextCloud (`apps/paheko/paheko/`). Ensuite Paheko est affiché dans une iframe de NextCloud.

Paheko est configuré pour ne permettre l'accès que depuis l'iframe à l'intérieur de NextCloud.

## Mises à jour

Paheko étant une application externe à NextCloud, la mise à jour se passe dans Paheko, et non pas dans NextCloud.

## Stockage et sauvegarde des données

Paheko n'utilise pas le stockage de NextCloud. Les données de Paheko sont stockées dans la base de données située dans `apps/paheko/paheko/data/association.sqlite`.

Pensez à sauvegarder ce fichier en plus de la base de données de NextCloud, au risque de perdre les données de Paheko en cas de problème ou de déménagement.

## Limitations techniques

* NextCloud est très lent, et chaque requête à Paheko doit passer par NextCloud, donc Paheko sera aussi lent que NextCloud (alors que normalement Paheko est bien plus rapide). NextCloud rajoute au moins une seconde à chaque clic / action ! Voir [cette comparaison entre Paheko et NextCloud](https://paheko.cloud/nextcloud) pour les détails.
* L'accès à la gestion du site web est désactivée, car le site web public ne serait pas accessible dans tous les cas.
* Les pages publiques des extensions (par exemple la réservation de créneau) ne seront pas accessibles.
* La gestion de documents est désactivée (doublon avec NextCloud).
* L'accès aux fichiers de Paheko en WebDAV ne fonctionnera pas.
* Les fichiers stockés dans Paheko (membres, écritures) ne seront pas accessibles dans NextCloud.
* Le journal d'audit ne donnera pas le nom des personnes qui ont modifié/supprimé un membre, une écriture, etc.

Il n'est donc pas forcément très conseillé d'utiliser cette app, à part si vous tenez à tout prix à intégrer Paheko dans NextCloud. Mais les développeurs⋅euses de Paheko conseillent plutôt d'installer Paheko séparément.

## Support

Aucun support ne sera apporté par e-mail par l'équipe de Paheko sur les problèmes d'installation / de comptabilité avec NextCloud. Mais les patchs restent les bienvenus. De même, toute personne qui souhaiterait assurer la maintenance / support de ce code est la bienvenue :-)

Merci d'utiliser les listes d'entraide pour toute question ou problème sur l'utilisation de Paheko : <https://fossil.kd2.org/paheko/wiki?name=Entraide>
