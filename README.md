# Application Paheko pour NextCloud

Permet d'installer Paheko comme application NextCloud, pour gérer membres et comptabilité.

Statut : alpha, utilisation à vos risques et périls.

**Attention : chaque requête transite par NextCloud, qui est terriblement lent (10 à 100 fois plus lent que Paheko seul), l'expérience utilisateur sera donc mauvaise.**

## Installation

1. Télécharger le code
2. Décompresser l'archive dans le répertoire `apps/paheko` de NextCloud.
3. Activer l'app Paheko dans la configuration de NextCloud.
4. Se rendre sur le menu de l'application Paheko dans NextCloud.

## Limitations techniques

* L'accès à la gestion du site web est désactivée, car le site web public ne serait pas accessible dans tous les cas.
* Les pages publiques des extensions (par exemple la réservation de créneau) ne seront pas disponibles.
* Les mises à jour seront à effectuer manuellement.
* La gestion de documents est désactivée (doublon avec NextCloud).
* L'accès aux fichiers de Paheko en WebDAV ne fonctionnera pas.
* Les fichiers stockés dans Paheko (membres, écritures) ne seront pas accessibles dans NextCloud.
* Le journal d'audit ne donnera pas le nom des personnes qui ont modifié/supprimé un membre, une écriture, etc.

## Comment ça fonctionne ?

Cette app ne fait que télécharger et installer Paheko dans un sous-répertoire de NextCloud. Ensuite Paheko est affiché dans une iframe de NextCloud.

Paheko est configuré pour ne permettre l'accès que depuis l'iframe dans NextCloud.
