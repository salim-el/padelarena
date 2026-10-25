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

## Version bêta — v0.2.0-beta (prévue début décembre 2026)

Dates cibles. Chaque branche est créée au moment où le travail commence.

| Période | Branche | Objectif |
|---|---|---|
| 27/10 – 07/11 | `feature/stripe-payment` | Paiement réel en mode test Stripe, confirmation par notification signée |
| 03/11 – 14/11 | `feature/split-payment` | Partage du prix entre les quatre joueurs, invitations, carte de garantie |
| 10/11 – 21/11 | `feature/arena-mode` | Mode Arena : scénarios, zones lumineuses, score de la partie |
| 17/11 – 26/11 | `feature/court-management` | Tarifs, terrains, fermetures exceptionnelles gérés par le gérant |
| 17/11 – 28/11 | `feature/open-matches` | Parties ouvertes et liste d'attente |
| 24/11 – 01/12 | `feature/accessibility-audit` | Contrôle WCAG 2.1 AA de tous les écrans, corrections |
| 01/12 – 05/12 | `release/0.2.0-beta` | Tests de bout en bout, documentation, étiquette `v0.2.0-beta` |
