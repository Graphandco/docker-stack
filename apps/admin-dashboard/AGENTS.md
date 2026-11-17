# Admin Dashboard — AGENTS.md (orchestrateur)

Contexte pour les agents IA travaillant sur ce monorepo.

## Structure

| Dossier | Rôle |
|---------|------|
| `admin-api/` | API Express (Docker socket, MySQL, WP-CLI, monitoring) |
| `admin-panel/` | UI Next.js (prod + mode dev parallèle) |

Chaque sous-projet a son propre `AGENTS.md` avec le détail des changements.

## Règle obligatoire pour les agents

**Toute modification ou ajout futur doit être consigné** dans le fichier `AGENTS.md` du dossier concerné :

1. Créer `AGENTS.md` s’il n’existe pas encore dans le dossier touché.
2. Éditer l’existant : ajouter une entrée datée (YYYY-MM-DD) avec résumé + fichiers clés.
3. Si le changement est transversal (API + UI), mettre à jour **les deux** `AGENTS.md` (`admin-api` et `admin-panel`), et une ligne d’index ici si c’est une feature majeure.
4. Ne pas y coller de secrets (tokens, mots de passe, `.env`).

Format d’entrée recommandé :

```md
### YYYY-MM-DD — Titre court
- **Agent** : `<uuid>` ou session courante
- **Quoi** : …
- **Fichiers** : `path/a`, `path/b`
```

## Environnement

- **Prod panel** : `vps.graphandco.com` — image `dockerhub.graphandco.com/admin-panel`
- **Dev panel** : `dev.vps.graphandco.com` — `docker-compose.dev.yml` (hot-reload)
- **API** : conteneur `admin-api`, rebuild local fréquent (`docker compose build && up -d`)
- Préférer le **panel dev** pour le travail UI ; rebuild prod seulement si demandé
- Ne pas committer sans demande explicite

## Déploiements

La page **Déploiements** (`/deploy`) gère uniquement le **redeploy registry**
(edit compose → pull tag → recreate) pour les images du registry privé
(`dockerhub.graphandco.com`).

L’ancien flux `deploy-runner` / GitHub Actions (`deploy.sh`) a été retiré.

### Build panel (prod)

```bash
npm run docker:deploy --prefix /home/graphandco/docker-stack/apps/admin-dashboard/admin-panel
```

(`build` + `push` registry + `up -d --force-recreate`)

### Rebuild API (local)

```bash
cd /home/graphandco/docker-stack/apps/admin-dashboard/admin-api
docker compose build admin-api && docker compose up -d admin-api
```

## Index des features majeures (sessions documentées)

| Feature | Où | Agents |
|---------|-----|--------|
| Sidebar : ouverture sous-menus au clic | `admin-panel` | `8cd1cb82-…` |
| Monitoring sites (vitesse, favicons, alertes) | api + panel | `b817ba01-…` |
| WordPress dashboard / plugins (Pro, multisite) | api + panel | `b817ba01-…` |
| SeaweedFS (buckets VPS/NAS + perso) | api + panel | `b817ba01-…` |
| Panel-dev start/stop depuis prod | api + panel | `b817ba01-…` |
| VPS Stats refactor | api + panel | `b817ba01-…` |
| Docker Volumes | api + panel | `b817ba01-…` |
| Journal Activité + docker events | api + panel | `b817ba01-…` |
| Fond Aura Glass Moonstone | panel | `b817ba01-…` |
| WP-CLI en `www-data` | api | `b817ba01-…` |
| Retrait deploy-runner / GHCR deploy.sh | api + panel | `b817ba01-…` |
| SeaweedFS : bucket WordPress | api + panel | session courante |
| SeaweedFS : sous-titre neutre | panel | session courante |

## Agents sources de ce journal initial

- [`8cd1cb82-15b1-4c84-9d09-655b0ca7744c`](8cd1cb82-15b1-4c84-9d09-655b0ca7744c) — sidebar clic vs hover
- [`b817ba01-c8d0-4304-bd9a-88eb93346ee0`](b817ba01-c8d0-4304-bd9a-88eb93346ee0) — bulk features dashboard (août–oct 2026)

Détail : voir `admin-api/AGENTS.md` et `admin-panel/AGENTS.md`.

### 2026-10-06 — Mise en place de ce système AGENTS.md
- **Agent** : `b817ba01-c8d0-4304-bd9a-88eb93346ee0`
- **Quoi** : orchestrateur racine + journaux `admin-api` / `admin-panel` documentant les sessions `8cd1cb82-…` et `b817ba01-…` ; contenu de `DEPLOY.md` intégré ici (section Déploiements).
