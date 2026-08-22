#!/bin/bash
# ===================================================================
# ClassPortal — Safe Deployment Script
# ===================================================================
# Handles local modifications before pulling updates to prevent
# merge conflicts with runtime data files (brain_db.json, .env, etc.)
# ===================================================================

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*"
}

error_log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERROR: $*" >&2
}

log "=============================================="
log "  ClassPortal — Deploying Updates..."
log "=============================================="

# --- Step 1: Sanity checks ---
log "[1/6] Running pre-deployment checks..."

if ! git rev-parse --is-inside-work-tree > /dev/null 2>&1; then
    error_log "Not a git repository. Aborting."
    exit 1
fi

REMOTE="origin"
BRANCH="main"

# Verify remote exists
if ! git remote get-url "$REMOTE" > /dev/null 2>&1; then
    error_log "Remote '$REMOTE' not configured. Aborting."
    exit 1
fi

log "  -> Repository: $(git remote get-url "$REMOTE")"
log "  -> Branch: $BRANCH"

# --- Step 2: Protect runtime data files ---
log "[2/6] Protecting runtime data files..."

# These files contain runtime/user-generated data — preserve them before merge
PROTECTED_FILES=(
    "legacy-app/brain_db.json"
    "brain_db.json"
    ".env"
)

for f in "${PROTECTED_FILES[@]}"; do
    if [ -f "$f" ]; then
        cp "$f" "${f}.deploy-backup" 2>/dev/null || true
        log "  -> Backed up: $f"
    fi
done

# --- Step 3: Fetch latest ---
log "[3/6] Fetching latest from remote..."
git fetch "$REMOTE" 2>&1 || {
    error_log "Fetch failed. Check network/remote access."
    exit 1
}
log "  -> Fetch succeeded."

# --- Step 4: Stash local changes ---
log "[4/6] Stashing local modifications..."
STASH_NAME="deploy-stash-$(date +%Y%m%d%H%M%S)"
HAD_CHANGES=false

if [[ -n $(git status --porcelain) ]]; then
    # Stash only tracked-file changes; untracked/ignored files (brain_db.json, .env) are safe
    git stash push --message "$STASH_NAME" 2>/dev/null || {
        log "  -> Stash failed (may be empty). Continuing..."
    }
    # Check if stash was actually created
    if git stash list | grep -q "$STASH_NAME"; then
        HAD_CHANGES=true
        log "  -> Tracked-file changes stashed as: $STASH_NAME"
    else
        log "  -> No stash needed (only untracked/ignored file changes)."
    fi
else
    log "  -> No local changes found. Skipping stash."
fi

# --- Step 5: Merge/Rebase ---
log "[5/6] Merging updates from $REMOTE/$BRANCH..."
PULL_FAILED=false

# Try fast-forward merge; fall back to merge commit
if git merge --ff-only "$REMOTE/$BRANCH" 2>&1; then
    log "  -> Fast-forward merge succeeded."
else
    log "  -> Fast-forward not possible. Attempting merge commit..."
    if git merge "$REMOTE/$BRANCH" --no-edit 2>&1; then
        log "  -> Merge succeeded."
    else
        PULL_FAILED=true
        error_log "Merge failed! Possible conflicts."
        
        # Restore stashed changes if any
        if [[ "$HAD_CHANGES" == true ]]; then
            log "  Restoring stashed changes..."
            git stash pop 2>/dev/null || true
        fi
        
        error_log "Deployment aborted. Resolve conflicts manually, then run:"
        error_log "  git merge --continue"
        error_log "  bash deploy.sh"
        exit 1
    fi
fi

# --- Step 6: Restore & post-deploy ---
log "[6/6] Running post-deploy tasks..."

# Restore protected files from backup (preserves production data over what Git pulled)
for f in "${PROTECTED_FILES[@]}"; do
    if [ -f "${f}.deploy-backup" ]; then
        cp "${f}.deploy-backup" "$f"
        rm -f "${f}.deploy-backup"
        log "  -> Restored production data: $f"
    fi
done

# Restore stashed tracked-file changes on top of new code
if [[ "$HAD_CHANGES" == true ]]; then
    if git stash list | grep -q "$STASH_NAME"; then
        log "  -> Restoring stashed tracked-file changes..."
        if git stash pop 2>&1; then
            log "  -> Tracked changes restored successfully."
        else
            log "  -> WARNING: Conflict during stash pop!"
            log "  -> Resolve conflicts manually, then run: git stash drop"
        fi
    fi
fi

# --- AI provider auto-repair ---
# Fixes known-dead AI configs automatically so generation keeps working.
log "  -> Checking AI provider configuration..."
if [ -f .env ]; then
    HAS_OVERRIDE=$(php -r 'try { $d = json_decode(@file_get_contents("brain_db.json"), true); echo (!empty($d["ai"]["api_key"]) && !empty($d["ai"]["base_url"])) ? "yes" : "no"; } catch (\Throwable $e) { echo "no"; }' 2>/dev/null || echo "no")

    if [ "$HAS_OVERRIDE" = "yes" ]; then
        log "     Admin AI override active in brain_db.json - .env untouched."
    else
        DEAD_CONFIG=0
        grep -q '^OPENAI_MODEL=zai-glm-4.7' .env && DEAD_CONFIG=1
        if grep -q '^OPENAI_BASE_URL=https://api.cerebras.ai' .env && grep -q '^OPENAI_API_KEY=csk-' .env; then
            DEAD_CONFIG=1
        fi

        if [ "$DEAD_CONFIG" = "1" ]; then
            log "     Dead AI configuration detected. Repairing to Groq (openai/gpt-oss-120b)..."
            sed -i 's|^OPENAI_BASE_URL=.*|OPENAI_BASE_URL=https://api.groq.com/openai|' .env
            sed -i 's|^OPENAI_MODEL=.*|OPENAI_MODEL=openai/gpt-oss-120b|' .env
            grep -q '^OPENAI_TPM_BUDGET=' .env || echo 'OPENAI_TPM_BUDGET=7800' >> .env

            if ! grep -q '^OPENAI_API_KEY=gsk-' .env; then
                if [ -t 0 ]; then
                    printf "     Paste your Groq API key (from console.groq.com/keys), or Enter to skip: "
                    read -r GROQ_KEY
                else
                    GROQ_KEY=""
                fi
                if [ -n "$GROQ_KEY" ]; then
                    sed -i "s|^OPENAI_API_KEY=.*|OPENAI_API_KEY=${GROQ_KEY}|" .env
                    log "     API key updated."
                else
                    log "     SKIPPED KEY: paste your Groq key later in Admin > Settings > AI Provider."
                fi
            fi
            log "     AI provider repaired."
        else
            log "     AI config looks OK."
        fi
    fi
fi

# Laravel post-deploy tasks
if [ -f artisan ]; then
    log "  -> Running migrations..."
    php artisan migrate --force 2>&1 || log "  -> Migration skipped (non-fatal)."
    
    log "  -> Clearing cache..."
    php artisan optimize:clear 2>&1 || log "  -> Cache clear skipped (non-fatal)."
    
    log "  -> Rebuilding frontend assets..."
    if command -v npm > /dev/null 2>&1; then
        npm run build 2>&1 || log "  -> npm build skipped (non-fatal)."
    else
        log "  -> npm not found. Skipping asset build."
    fi
    
    log "  -> Generating optimized cache..."
    php artisan optimize 2>&1 || log "  -> Optimize skipped (non-fatal)."
else
    log "  -> No artisan found. Skipping Laravel post-deploy tasks."
fi

log "=============================================="
log "  Deployment complete!"
log "  $(date)"
log "=============================================="
