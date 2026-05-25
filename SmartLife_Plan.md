# Smart Life — Sales & Stock Mini ERP — Build Plan

CodeIgniter 3 test. Goal: small, clean, runnable. Reviewers prefer a tight scope done well over a sprawling half-broken thing.

---

## 1. The brief (translated, condensed)

Build a small ERP in CI3 with:

**Core**
- Products: list, search by name/code, category filter, pagination, add/edit/disable.
- Stock: multiple warehouses, qty per (product, warehouse), view per-warehouse qty.
- Sales invoice: pick customer, pick warehouse, search/add products, edit qty, delete line, live totals on the front end, save invoice + lines.

**Extra (do after core)**
- Block sale if stock insufficient.
- Percentage discount with proper rounding.
- Low-stock report (`quantity <= alert_quantity`) with warehouse filter, search, shortage column.

**Permissions**
- `admin` sees all warehouses.
- `user_warehouse` sees only their own.

**Advanced**
- Solve (or explain) race condition on the last unit being sold by two parallel requests.
- Invoice list + details page.
- CSV export for low-stock report.

**Deliverables**
- Runnable project + run instructions.
- Decisions / done / not-done doc.
- More than one commit.
- SQL file with schema.

---

## 1.5 Locked decisions (confirmed)

- **Discount:** invoice level, one `%` applied to subtotal. No per-line discount.
- **UI language:** bilingual (Arabic + English) with a switcher. RTL handled via `dir` attribute + Bootstrap RTL stylesheet toggled by language.
- **Sale price on invoice line:** read-only and pulled from product for `user_warehouse`. Editable for `admin`. Server must re-validate this — don't trust the client.
- **Seed data:** full seed — admin + warehouse users, 2 warehouses, 3 categories, ~12 products, stock rows (some intentionally below `alert_quantity`), 2-3 customers. Reviewer runs `schema.sql` then `seed.sql`, logs in, and sees a working app.

---

## 2. Tech stack & constraints to keep in mind

- **CI3 is legacy.** No service container, no Eloquent, no migrations CLI worth using. Don't try to make it look like Laravel — just use its idioms (controllers, models, libraries, helpers).
- **PHP 7.4+** — typed properties OK, no constructor promotion (8.0), no enums (8.1). Stick to 7.4 syntax to be safe.
- **DB:** InnoDB only (we need transactions and row locks). MyISAM would silently break the concurrency story.
- **JS:** jQuery is fine here. CI3 + jQuery is the natural pairing and reviewers won't be surprised. Don't pull React.
- **No Composer maximalism.** One or two libs at most. Reviewer running the project should `git clone`, import SQL, set DB config, done.

---

## 3. Database schema

Single SQL file at `/database/schema.sql`. InnoDB, utf8mb4.

```sql
-- users
id, username, password_hash, role ENUM('admin','user_warehouse'), warehouse_id NULL, is_active, created_at

-- warehouses
id, name, code UNIQUE, is_active, created_at

-- categories
id, name, is_active, created_at

-- products
id, code UNIQUE, name, category_id FK, price DECIMAL(12,2),
alert_quantity INT DEFAULT 0, is_active TINYINT DEFAULT 1, created_at, updated_at

-- stock  (composite unique on product_id+warehouse_id)
id, product_id FK, warehouse_id FK, quantity INT NOT NULL DEFAULT 0
UNIQUE KEY (product_id, warehouse_id)

-- customers
id, name, phone, is_active, created_at

-- invoices
id, invoice_no UNIQUE, customer_id FK, warehouse_id FK, user_id FK,
subtotal DECIMAL(12,2), discount_percent DECIMAL(5,2) DEFAULT 0,
discount_amount DECIMAL(12,2) DEFAULT 0, total DECIMAL(12,2),
created_at

-- invoice_lines
id, invoice_id FK, product_id FK, qty INT, unit_price DECIMAL(12,2),
line_total DECIMAL(12,2)
```

**Decisions worth noting:**
- `quantity INT` not decimal — assumption: whole units. Mention this in DECISIONS.md.
- `is_active` flag instead of soft-delete. "Disable" in the brief = hide from selection, keep history intact.
- Discount stored as both percent and computed amount. Lets us recompute or audit later without re-deriving.
- `invoice_no` separate from PK so we can format it (e.g. `INV-2026-000123`) without exposing IDs.
- Stock row per (product, warehouse). Auto-created on first stock-in. Don't pre-seed every product × warehouse pair.

---

## 4. Architecture choices

### 4.1 Layering

| Option | Pros | Cons |
|---|---|---|
| A. Pure CI3 (fat controllers, basic models) | Fast, idiomatic | Invoice logic gets messy in controller |
| B. Service classes under `application/libraries/` | Cleaner invoice logic, testable | Slight ceremony |
| C. Repository pattern + services | Most Laravel-like | Over-engineered for this scope |

**Recommend: B.** Keep controllers thin. Put invoice creation in `Invoice_service` library — that's where the transaction, stock check, and totals live. Everything else stays in models.

### 4.2 Auth

| Option | Pros | Cons |
|---|---|---|
| A. Roll own session-based auth | Simple, no deps | Have to be careful with password hashing |
| B. Ion Auth library | Battle-tested | Heavy for this test, adds noise |
| C. JWT | — | Pointless for server-rendered app |

**Recommend: A.** Native sessions + `password_hash`/`password_verify`. 50 lines. A `MY_Controller` base class checks login; an `Admin_Controller` checks role.

### 4.3 Frontend

Server-rendered everything, except the invoice screen which needs AJAX for:
- Product search dropdown
- Adding lines and computing totals live

Use Bootstrap 5 from CDN. One global `assets/js/app.js`, plus a dedicated `invoice.js`.

### 4.4 Validation

CI3's `form_validation` for input shape. Business rules (e.g. stock check) in the service layer. Don't try to express stock checks in `form_validation` — wrong tool.

### 4.5 Bilingual UI (English + Arabic, RTL)

Don't overthink this. CI3 has a built-in language system, use it.

```
application/language/english/ui_lang.php
application/language/arabic/ui_lang.php
```

Pattern:
```php
$lang['products_title'] = 'Products';
$lang['btn_save']       = 'Save';
// ...one file is enough for the whole app at this scale
```

In views: `<?= lang('products_title') ?>`. No hardcoded English in views — even if you're tired at 11pm, take the 5 extra seconds.

**Language switch:**
- Two links in the navbar: `EN | AR`. Each hits `/lang/set/en` or `/lang/set/ar` and stores choice in session, then redirects back.
- Default to English on first visit, or read `Accept-Language` if you want to be nice.

**RTL:**
- Layout template reads session lang. If `ar`:
  - `<html dir="rtl" lang="ar">`
  - Load Bootstrap RTL build: `bootstrap.rtl.min.css` instead of `bootstrap.min.css`
  - Add a body class `lang-ar` so you can target one or two custom tweaks (mostly icons that need flipping)

**Numbers and dates:**
- Keep numbers in Western digits (`1,234.50`) in both languages. Eastern Arabic numerals look authentic but break copy-paste into Excel.
- Dates: `Y-m-d H:i` everywhere. No locale formatting headaches.

**What NOT to do:**
- Don't translate database content (product names, categories). Brief doesn't require it. Store as entered, display as entered.
- Don't translate error messages from CI3 internals — you'll be there all night. Translate your own UI labels + your own validation messages only.
- Don't use `gettext` / `.po` files. Overkill.

Time cost: ~1.5h if you write language keys as you build each view. ~3h if you leave it for the end and have to retrofit. **Do it as you go.**

---

## 5. The concurrency problem (the most important "advanced" item)

The reviewer will read this section first in DECISIONS.md. Get it right.

**Problem:** Two requests both read `quantity = 1`, both pass the check, both decrement to `0`, then one decrements to `-1`.

**Options:**

| Approach | How it works | Verdict |
|---|---|---|
| A. `SELECT ... FOR UPDATE` inside transaction | Lock the stock row, read, check, update, commit | Clean, easy to explain |
| B. Optimistic locking (version column) | Read version, update WHERE version = X, retry on fail | More moving parts, needs retry loop |
| C. Atomic conditional UPDATE | `UPDATE stock SET qty = qty - N WHERE product_id=? AND warehouse_id=? AND qty >= N` then check `affected_rows` | Simplest, one query per line, no SELECT needed |

**Recommend: C, with A as the wrapping pattern.**

Flow:
```
BEGIN;
  -- for each line:
  UPDATE stock SET quantity = quantity - :qty
  WHERE product_id = :pid AND warehouse_id = :wid AND quantity >= :qty;
  -- if affected_rows == 0 → ROLLBACK, return "insufficient stock for X"
INSERT invoice, INSERT invoice_lines
COMMIT;
```

Why C: no read-then-write window, no explicit lock to manage, works under any isolation level. The `quantity >= :qty` predicate is the lock.

Document in DECISIONS.md: tested by running two parallel curl requests buying the last unit — only one succeeds.

---

## 6. Permissions

Two roles. Keep enforcement at two layers:

1. **Controller guard.** `Admin_Controller` rejects non-admins. Warehouse-scoped controllers check `user->warehouse_id` matches the requested resource.
2. **Query scoping.** All stock/invoice queries pass through a helper that injects `WHERE warehouse_id = ?` when the user is `user_warehouse`. Don't trust the form to send the right warehouse_id.

When a `user_warehouse` opens the invoice screen, the warehouse dropdown is locked to their own warehouse (`disabled` + hidden input).

---

## 7. Discount + rounding + line pricing

**Discount: invoice level only.** Single `%` on subtotal. Round to 2 decimals, `PHP_ROUND_HALF_UP`. Use `bcadd`/`bcmul` if you want to be paranoid, but `round()` on `DECIMAL(12,2)` values is fine here.

```
subtotal = sum(line_total)
discount_amount = round(subtotal * discount_percent / 100, 2)
total = subtotal - discount_amount
```

Compute on both client (for live display) and server (source of truth). Never trust the client's total.

**Line unit price:**
- For `user_warehouse`: price input is `readonly` (or hidden, with price shown as text). Server forces `unit_price = product.price` regardless of what's posted.
- For `admin`: price input is editable. Server accepts the posted value but validates it's a positive number with at most 2 decimals.

In the controller, after fetching the user role:

```php
$isAdmin = $this->auth->user()->role === 'admin';
foreach ($lines as &$line) {
    $product = $this->products_m->get($line['product_id']);
    if (!$isAdmin) {
        $line['unit_price'] = $product->price;
    }
    $line['line_total'] = round($line['unit_price'] * $line['qty'], 2);
}
```

This is the kind of small business rule that's easy to forget and easy for a reviewer to test. Don't skip it.

---

## 8. CSV export

Low-stock report. Native `fputcsv` to `php://output`. Headers:

```
Content-Type: text/csv; charset=utf-8
Content-Disposition: attachment; filename="low_stock_2026-05-24.csv"
```

Prepend UTF-8 BOM (`\xEF\xBB\xBF`) so Excel opens Arabic correctly. This is the kind of small detail that signals you've shipped Arabic apps before.

---

## 9. Commit plan

Aim for ~12-15 commits. Each one should be a complete, runnable step. Suggested order:

1. `chore: init CI3 skeleton, base config, .gitignore`
2. `feat: db schema + seed SQL (users, warehouses, categories, products, stock, customers)`
3. `feat: auth (login/logout, MY_Controller, password hashing)`
4. `feat: bilingual UI scaffolding (lang files, switcher, RTL toggle)`
5. `feat: categories CRUD`
6. `feat: products list with search, filter, pagination`
7. `feat: products add/edit/disable`
8. `feat: warehouses CRUD`
9. `feat: stock view per warehouse + adjust stock`
10. `feat: customers CRUD`
11. `feat: invoice screen UI + product search AJAX (admin-editable price)`
12. `feat: invoice save with transaction + stock decrement (concurrency-safe)`
13. `feat: invoice list + details page`
14. `feat: low-stock report + warehouse filter + CSV export`
15. `feat: warehouse-scoped permissions`
16. `docs: README + DECISIONS + screenshots`

Commit messages stay terse. No "🎉 Added amazing new feature ✨". Conventional commits style is fine but optional — reviewers care that history is logical, not formatted.

**Don't squash.** They explicitly want to see more than one commit.

**Tip:** commit the i18n scaffolding early (commit #4) so every later view starts with `lang()` calls from day one. Retrofitting i18n is the most boring 2 hours you'll ever spend.

---

## 10. Files in the repo

```
/application/
  controllers/         (Auth, Products, Categories, Warehouses, Stock, Customers, Invoices, Reports)
  models/
  libraries/           (Invoice_service.php, Auth_lib.php)
  views/
  config/database.php  (with env override note)
/assets/
  css/, js/, vendor/   (bootstrap from CDN, no vendor folder needed)
/database/
  schema.sql
  seed.sql             (admin + 2 warehouse users, 2 warehouses, 3 categories,
                        ~12 products with codes/prices/alert_quantity,
                        stock rows per (product, warehouse) — some intentionally
                        below alert level to demo the report, 2-3 customers)
/system/                (CI3 system — committed, standard practice)
README.md
DECISIONS.md
.gitignore
```

---

## 11. README.md (what to put in it)

Keep it to one screen. Sections:

- What this is (1 line)
- Requirements (PHP 7.4+, MySQL 5.7+/MariaDB 10.3+, Apache/Nginx, mod_rewrite)
- Setup steps (clone → create DB → import schema.sql → import seed.sql → edit `application/config/database.php` → edit `config.php` base_url → run via `php -S localhost:8000 -t .` or Apache)
- Default credentials (admin / admin123, warehouse1 / pass123)
- Notes about `index.php` removal via .htaccess

---

## 12. DECISIONS.md (what to put in it)

This is the doc that separates a senior submission from a junior one. Sections:

- **What's done** — checklist mirroring the brief
- **What's not done and why** — be honest, e.g. "skipped per-line discount because brief was ambiguous, chose invoice-level discount instead"
- **Key decisions:**
  - Why `is_active` not soft delete
  - Why invoice-level discount
  - Why integer quantity
  - How concurrency is handled (the SQL pattern from §5, with the test you ran)
  - Why no Ion Auth
- **Known limitations** — e.g. no tax/VAT, no multi-currency, no audit log, no stock transfers between warehouses
- **What I'd add next** — see §14

---

## 13. Time budget (suggested order if tomorrow gets tight)

If you can't finish everything, finish in this order:

1. Schema + seed + auth + i18n scaffolding (4h)
2. Products + stock view (3h)
3. Invoice screen + save with stock decrement + admin-editable price (3h)
4. Concurrency fix + DECISIONS writeup of it (1h)
5. Low-stock report + CSV (1.5h)
6. Permissions enforcement (1h)
7. README + screenshots + DECISIONS polish (1h)

That's ~14.5h. Realistic for a full focused day, tight for a half day.

**If you're short on time, cut in this order:**
1. CSV export (mention as "not done, would take ~30min" in DECISIONS)
2. Customer CRUD — keep seed customers only, no add/edit screen
3. Category CRUD — same, seed only
4. Invoice list page — skip if you must, leave just the save

**Never cut:**
- Concurrency handling (the differentiator)
- Permissions (the brief calls it out specifically)
- i18n (you committed to it, halfway-done is worse than not promising it)
- README + DECISIONS (reviewers read these first)

---

## 14. Stretch improvements (mention in DECISIONS, don't build)

Things a real ERP would need, signals you understand the domain:

- Audit log on stock changes
- Stock transfer between warehouses
- VAT/tax per product or per invoice
- Multi-currency with FX snapshot per invoice
- Invoice cancel / return flow (with stock restore)
- Per-line discount on top of invoice discount
- Soft delete via `deleted_at` for products with historical invoices
- Index on `invoices.created_at` and `invoice_lines.invoice_id` (actually do add the FK index, just mention more advanced ones)
- Background queue for CSV export when reports get large

---

## 15. Code style reminders (so it reads as human, not AI)

- **No tutorial comments.** Don't write `// loop through products`. Write the loop.
- Comments only where the *why* isn't obvious from the code. The concurrency UPDATE is one such place — comment it.
- Realistic names. `$p` in a 3-line loop is fine. `$productEntityCollection` is not.
- Don't catch exceptions you can't handle. Let CI3's error page show in dev.
- No defensive null-checks on values that can't be null per schema.
- Keep functions short, but don't extract a one-liner into its own method just because.
- Mixed quotes (`'` for plain strings, `"` only when interpolating) — standard PHP style, not a religion.
- Skip the docblocks on obvious getters. Use them where the param/return types actually need explaining.
- Indentation: 4 spaces (CI3 convention). Don't fight it.

---

## 16. Quick gotchas with CI3 to remember

- `index.php` in URLs — set up `.htaccess` early or it'll annoy you all day.
- CSRF: enable in `config.php`. For AJAX POSTs, send the token in headers — write a small jQuery `ajaxSetup` to attach it automatically.
- Sessions: use database driver, not files, if you want them to survive properly on shared hosting. For this test, files driver is fine.
- `$this->db->trans_start()` / `trans_complete()` works, but for the concurrency-critical block use `trans_begin()` / `trans_commit()` / `trans_rollback()` so you can inspect `affected_rows` between queries.
- `query_builder` is fine for most things. Raw `query()` with bindings for anything non-trivial — don't fight QB to write a JOIN it doesn't want to write.
