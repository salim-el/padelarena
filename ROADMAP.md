# Feuille de route

## Conventions

**Branches** (Git Flow)

| Branche | Rôle |
|---|---|
| `main` | Versions livrées uniquement, chacune marquée d'une étiquette (`v0.1.0-alpha`, `v0.2.0-beta`…) |
| `develop` | Intégration des fonctionnalités terminées |
| `feature/<sujet>` | Une fonctionnalité, créée depuis `develop`, fusionnée dans `develop` |
| `bugfix/<sujet>` | Correction d'une anomalie trouvée sur `develop` |
| `release/<version>` | Préparation d'une version, fusionnée dans `main` et dans `develop` |
| `hotfix/<sujet>` | Correction urgente d'une version livrée, créée depuis `main` |

Noms en anglais, en minuscules, mots séparés par des tirets.

**Messages de commit** (Conventional Commits)

`type(portée): description courte en français`, avec `feat`, `fix`, `style`, `docs`, `test`, `refactor` ou `chore`.

## Version alpha — v0.1.0-alpha (livrée)

| Branche | Contenu |
|---|---|
| `feature/database-schema` | Tables du domaine, modèles, données de démonstration |
| `feature/planning` | Planning public, prix, navigation par jour |
| `feature/booking` | Réservation, paiement simulé, facture, annulation |
| `feature/access-kiosk` | QR code personnel, borne d'accès |
| `feature/admin-dashboard` | Tableau de bord du gérant, gestion des comptes |
| `feature/ui-theme` | Charte graphique du T10, composants visuels |
| `feature/i18n-fr` | Interface et e-mails en français |
| `feature/password-policy` | Mot de passe de 15 caractères minimum |
| `bugfix/confirm-password-throttle` | Limite d'essais sur la confirmation du mot de passe |

## Déjà sur `develop` (entrera dans la bêta)

| Branche | Contenu |
|---|---|
| `feature/home-page`, `feature/home-images` | Page d'accueil publique, photos |
| `feature/app-visual-refresh`, `bugfix/planning-cell-size` | Nouveau visuel des écrans connectés |
| `feature/arena-game-mode`, `feature/auth-layout-and-arcade` | Mode arcade sur les six terrains (+14 €), choix au paiement ; écrans de connexion revus |
| `feature/arcade-zones` | Un seul mode Arcade, aligné sur le document de conception : zones Or (point double), Rouge (faute) et Bleue (points Arena) |

## Version bêta — v0.2.0-beta (prévue début décembre 2026)

Dates cibles. Chaque branche est créée au moment où le travail commence.

| Période | Branche | Objectif |
|---|---|---|
| 27/10 – 07/11 | `feature/stripe-payment` | Paiement réel en mode test Stripe, confirmation par notification signée |
| 03/11 – 14/11 | `feature/split-payment` | Partage du prix entre les quatre joueurs, invitations, carte de garantie |
| 10/11 – 21/11 | `feature/open-matches` | Annonce pour compléter une partie, liste d'attente (dépend du partage du prix) |
| 10/11 – 21/11 | `feature/arena-mode` | Moteur de jeu Arcade : 5 séquences de zones prédéfinies, point double, faute, point en or ; validation des rebonds sur tablette et simulateur web ; score en direct |
| 17/11 – 26/11 | `feature/equipment-rental` | Location de matériel ; factures à plusieurs lignes (TVA 21 % sur le matériel, 6 % sur le terrain) ; stock limité |
| 17/11 – 26/11 | `feature/court-management` | Tarifs, terrains, fermetures exceptionnelles gérés par le gérant |
| 24/11 – 01/12 | `feature/accessibility-audit` | Contrôle WCAG 2.1 AA de tous les écrans, corrections |
| 01/12 – 05/12 | `release/0.2.0-beta` | Tests de bout en bout, documentation, étiquette `v0.2.0-beta` |

## Idées à étudier après la bêta

| Idée | Intérêt | Difficulté |
|---|---|---|
| Créneaux flash | Remise automatique sur un terrain encore libre à moins de 3 h | Faible |
| Revanche en un clic | Proposer aux quatre joueurs de rejouer, avec leurs disponibilités communes | Faible |
| Réservation récurrente | Le même créneau chaque semaine pour un groupe fixe | Moyenne |
| Points Arena, défis et paliers | Fidélité du document de conception : points gagnés en partie, défis récompensés au bar, paliers de récompenses | Moyenne |
| Événement privé | Plusieurs terrains en même temps pour une entreprise ou un anniversaire, classement commun | Moyenne |
| Profil de progression | Records et zones réussies, à partir des événements mesurés par les LED | Moyenne |
| Mode Rotatif | 3 contre 3 sur un hexagone dont le sol tourne : nouveauté future, hors TFE, après validation de l'Arcade | Élevée |
| Ambiances lumineuses | Thèmes prédéfinis seulement, pour respecter la limite de clignotements du T03 | Moyenne |

Écartée : une musique choisie par les joueurs. Diffuser de la musique dans un lieu public demande une licence payante (Unisono en Belgique).
