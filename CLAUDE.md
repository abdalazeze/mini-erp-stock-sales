# CLAUDE.md — Project rules for Claude Code

Read this file before any work. Re-read it if you feel yourself drifting from the plan.

## What this is

A coding test submission for Smart Life. Mini Sales & Stock ERP in CodeIgniter 3. Plan lives in `SmartLife_Plan.md` — follow it. Brief is in `docs/Smart_Test.pdf`.

The reviewer is a senior dev. They will skim the code, read `DECISIONS.md` and `README.md`, run it locally, and run two parallel curl requests to test the concurrency claim. Optimize for that reading order.

## Hard rules

- **PHP 7.4 syntax only.** No 8.0+ features (no constructor promotion, no enums, no named args, no `match`, no `readonly`).
- **CI3 idioms only.** Controllers, models, libraries, helpers. Do not import Symfony components. Do not try to make CI3 look like Laravel.
- **MySQL 8 / MariaDB 10.3+.** InnoDB everywhere. Common-subset SQL only — no `RETURNING`, no MySQL-8-only syntax.
- **No npm.** Bootstrap from CDN, jQuery from CDN.
- **Composer is for autoload only**, if at all. If a library isn't critical, skip it. Ion Auth is not allowed — we're rolling simple session auth.
- **Bilingual from commit #4 onward.** Every view string goes through `lang('key')`. No hardcoded English in views, ever. Arabic translations can be placeholders initially but the keys must exist.
- **Stop after each commit** and wait for the user to review/test before moving to the next. Do not chain commits in one go.

## Code style — read this twice

The user is a senior PHP/Laravel dev. He will reject code that reads like AI-generated tutorial output. Specifically:

**Do NOT:**
- Write comments that restate the code (`// loop through products`).
- Add docblocks on obvious methods (`/** Get the product by ID */ public function get($id)`).
- Add defensive null checks on values that the schema guarantees are non-null.
- Catch exceptions you can't meaningfully handle. Let CI3's error page show in dev.
- Use ceremonial variable names (`$productEntityInstance`). Use `$product`.
- Add a "comprehensive guide" of 6 functions when 2 will do.
- Pre-optimize for cases the brief doesn't require (multi-currency, audit logs, etc.).
- Add try/catch blocks "just in case".
- Write `if ($x === true)` — write `if ($x)`.
- Wrap one-line operations in their own method "for clarity".

**Do:**
- Comment only where the *why* is non-obvious. The concurrency UPDATE is one such place — comment it. A `foreach` loop is not.
- 4 spaces indentation (CI3 convention).
- Single quotes for plain strings, double quotes only when interpolating.
- Short methods, but don't extract one-liners.
- Realistic names: `$p` inside a 3-line loop is fine; `$product` everywhere else.
- Mixed quoting styles where natural (`'name'` for arrays, `"Hello, $name"` for interpolation).

If you're about to write a 30-line method with 8 comments, you're probably doing too much. Trim it.

## Commit discipline

- Follow the commit list in `SmartLife_Plan.md` §9. Don't merge two commits into one.
- Commit messages: short, lowercase, conventional-commits style optional. No emojis.
- Each commit must leave the project in a runnable state (or close to it — auth commit may not be navigable yet, that's OK).
- Do not squash. The reviewer wants to see history.
- After each commit, stop and report: "Commit N done: [one-line summary]. Next: commit N+1. Proceed?"

## Concurrency — the critical part

`SmartLife_Plan.md` §5 has the strategy. Implement exactly that:

```sql
UPDATE stock SET quantity = quantity - :qty
WHERE product_id = :pid AND warehouse_id = :wid AND quantity >= :qty;
```

Then check `$this->db->affected_rows()`. If 0, rollback and return an "insufficient stock" error naming the product.

Use `$this->db->trans_begin() / trans_commit() / trans_rollback()`, NOT `trans_start/complete` — we need to inspect affected_rows between queries.

Write a comment above this block explaining why (atomic conditional update = no read-then-write race). This is the one place where a comment is mandatory.

After implementing, write a 30-line PHP script in `tests/concurrency_test.php` that uses `curl_multi` to fire 2 simultaneous sale requests for the last unit and prints the outcome. Run it. Paste the output in `DECISIONS.md`.

## Permissions — easy to get wrong

Two layers, both required:

1. **Controller guard** in a `MY_Controller` base class. Warehouse-scoped controllers check `$this->user->warehouse_id` matches requested resources.
2. **Query scoping**: when current user is `user_warehouse`, every stock/invoice query injects `WHERE warehouse_id = ?` with the user's warehouse. Don't trust form data.

The invoice screen for a warehouse user: warehouse dropdown is `disabled` with their warehouse pre-selected, and a hidden input carries the value. Server re-validates anyway.

## Pricing on invoice lines

- `user_warehouse`: price input is `readonly`. Server forces `unit_price = product.price` regardless of posted value.
- `admin`: price input editable. Server validates positive number, 2 decimals max.

This is the trap. Don't skip the server-side enforcement.

## i18n specifics

- Files: `application/language/english/ui_lang.php` and `application/language/arabic/ui_lang.php`.
- Load in `MY_Controller::__construct` based on session lang (default `en`).
- Switcher: `/lang/set/en` and `/lang/set/ar`, stores in session, redirects back via `$_SERVER['HTTP_REFERER']` fallback to `/`.
- Layout: `<html dir="<?= $this->session->userdata('lang') === 'ar' ? 'rtl' : 'ltr' ?>" lang="<?= ... ?>">`
- Bootstrap: load `bootstrap.rtl.min.css` when `lang === 'ar'`, otherwise `bootstrap.min.css`.
- Do NOT translate product/category/customer names in the database.
- Do NOT translate CI3 internal validation messages — only translate keys we author.

## What NOT to build

The brief is small on purpose. Stay in scope.

- No VAT/tax calculations.
- No multi-currency.
- No invoice cancel/return flow.
- No stock transfer between warehouses.
- No audit log.
- No password reset flow.
- No user management UI (seed users via SQL).
- No email notifications.
- No Docker setup (mention in README that it runs on plain PHP/Apache).
- No PHPUnit suite (the concurrency test script is the only test).

If something feels nice-to-have, list it in `DECISIONS.md` under "What I'd add next" instead of building it.

## Deliverables checklist (do not submit without)

- [ ] All commits pushed to a Git repo
- [ ] `README.md` with setup instructions and default credentials
- [ ] `DECISIONS.md` with: done list, not-done list, key decisions, concurrency explanation + test output, known limitations, next steps
- [ ] `database/schema.sql` and `database/seed.sql`
- [ ] `.gitignore` excluding `application/config/database.php` if it has real creds (use `database.php.example` pattern)
- [ ] Project runs from a fresh clone in under 5 minutes following the README

## When in doubt

- Re-read `SmartLife_Plan.md`.
- Ask the user one direct question. Don't guess on decisions that affect the schema.
- Default to "smaller and cleaner" over "more features".
