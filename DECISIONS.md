# DECISIONS.md

## What's done

- [x] Database schema — InnoDB throughout, FK constraints, indexes on every FK column
- [x] Seed data — admin + 2 warehouse users, 2 warehouses, 3 categories, 12 products, stock rows (some intentionally below `alert_quantity`), 3 customers
- [x] Session-based auth (login/logout, `password_hash`/`password_verify`)
- [x] Role-based access — `admin` and `user_warehouse`, enforced at two layers
- [x] Bilingual UI — English and Arabic with RTL toggle (Bootstrap RTL build swapped per session)
- [x] Language switcher (`/lang/set/en`, `/lang/set/ar`), stored in session
- [x] Categories CRUD (admin only, with enable/disable)
- [x] Products — list with search + category filter + pagination, add/edit/disable (admin only)
- [x] Warehouses CRUD (admin only)
- [x] Stock view per warehouse + manual adjustment (admin only)
- [x] Customers CRUD (admin only)
- [x] Invoice create — product search (AJAX/debounced), live totals (JS), line add/edit/remove
- [x] Pricing enforcement — `user_warehouse` price locked server-side to `product.price`; admin price validated positive + 2 decimals
- [x] Concurrency-safe stock decrement (atomic conditional UPDATE — see below)
- [x] Invoice list with pagination (warehouse-scoped for `user_warehouse`)
- [x] Invoice details page
- [x] Low-stock report with warehouse filter + product search
- [x] CSV export for low-stock report (UTF-8 BOM for Excel/Arabic compatibility)
- [x] Warehouse-scoped permissions — `user_warehouse` can only see/act on their own warehouse at both the controller and query level
- [x] CSRF protection (CI3 built-in, token injected as meta tag for AJAX calls)

---

## What's not done and why

- **No VAT/tax** — brief doesn't mention it; would need a tax table and per-line or per-invoice rate column. Listed in "next steps".
- **No multi-currency** — out of scope. Single currency implicit in `DECIMAL(12,2)` price columns.
- **No invoice cancel/return flow** — not in the brief. Would require reversing stock decrements and a status column.
- **No stock transfer between warehouses** — not in the brief.
- **No audit log** — not in the brief. Would add a `stock_movements` table tracking every adjustment and sale.
- **No password reset** — not in the brief. Users are seeded via SQL.
- **No user management UI** — not in the brief. Add users directly via SQL/seed.
- **No per-line discount** — the brief said "percentage discount"; interpreted as invoice-level. See decision below.
- **No PHPUnit suite** — the brief's only test requirement is the concurrency script.
- **No Docker setup** — plain PHP + Apache as mentioned in the brief. README covers both.
- **No email notifications** — not in the brief.

---

## Key decisions

### `is_active` flag, not soft-delete

Products and categories use an `is_active` tinyint. Soft-delete (`deleted_at`) would be the right call if these records are referenced by historical invoices and need to be hidden from the UI while remaining auditable. For this scope, `is_active` is simpler and sufficient — disabled products are hidden from lists and the invoice search but their rows still satisfy FK constraints on existing invoice lines.

If invoice history were the priority, I'd switch to `deleted_at` and filter `WHERE deleted_at IS NULL` everywhere. Noted as a limitation.

### Invoice-level discount

The brief says "percentage discount" without specifying granularity. Invoice-level (one `%` applied to the subtotal) is the most common interpretation in simple ERPs. Per-line discount would require an extra column on `invoice_lines` and more UI. Listed as a next-step if the business needs it.

### Integer quantity

Stock quantities are `INT` throughout. Fractional quantities (e.g., kg of fabric) would require `DECIMAL(12,3)` and a unit-of-measure column. The brief implies discrete units (electronics, furniture, office supplies in the seed). Easy to change — it's one column type and one `round()` call.

### Concurrency — atomic conditional UPDATE

**Problem:** Two requests both read `quantity = 1`, both pass the stock check, both decrement — one ends at `quantity = -1`.

**Solution used (approach C from the plan):**

```sql
UPDATE stock
SET    quantity = quantity - ?
WHERE  product_id = ? AND warehouse_id = ? AND quantity >= ?;
```

Followed immediately by `affected_rows()`. If 0, the row either doesn't exist or `quantity < requested qty` — either way, we `ROLLBACK` and return an error naming the product.

This is wrapped in an explicit `trans_begin / trans_commit / trans_rollback` block (not CI3's `trans_start/complete` — we need to inspect `affected_rows` between queries before committing).

**Why this beats SELECT … FOR UPDATE here:** The conditional UPDATE collapses the "read quantity, check it, decrement it" into a single atomic operation. Under any isolation level, two concurrent UPDATEs on the same row will serialize — one wins (affected_rows = 1), one loses (affected_rows = 0). No explicit row lock to acquire or release, no retry loop.

**Test:** `tests/concurrency_test.php` — uses `curl_multi` to fire two simultaneous POST requests against `/invoices/save`, each trying to buy the last unit. Both sessions are pre-logged-in; CSRF tokens fetched separately.

**Test output** (run against local PHP dev server with product ELEC-004 at qty=1):

```
Logging in as admin (two sessions)...
Firing 2 simultaneous requests for product #4...
Request 1 landed at: http://localhost:8000/invoices/view/3
Request 2 landed at: http://localhost:8000/invoices/create
Result: 1 succeeded, 1 rejected.
PASS — concurrency control working correctly.
```

### No Ion Auth

Ion Auth adds ~20 files and a separate schema migration just to replace 50 lines of session logic. Rolling our own with `password_hash`/`password_verify` is simpler, easier to read, and has no external dependencies. The only thing missing is password-reset emails — which the brief doesn't require.

---

## Known limitations

- No VAT calculation — prices are stored and displayed as-is.
- Quantity is integer only — no support for fractional units (kg, m, etc.).
- `is_active = 0` products still appear on existing invoice details (correct — they were active at sale time, but the FK row is still visible). A `deleted_at` with soft-delete semantics would handle this more cleanly.
- The concurrency test requires a running server and a manually set product qty. It's a smoke test, not an automated suite.
- No pagination on invoice lines or stock adjustment history — not needed at current seed scale.
- CSRF token for AJAX is read from a `<meta>` tag injected by the layout. This works but means a page reload is needed if the token expires mid-session.

---

## What I'd add next

- **Audit log** — `stock_movements` table: every sale decrement and manual adjustment with user + timestamp. Essential for a real ERP.
- **Stock transfer** — move qty between warehouses with a two-step UPDATE inside a transaction (same atomic pattern as sales).
- **VAT/tax** — per-product or per-invoice tax rate, separate line on the invoice total.
- **Invoice cancel/return** — status column (`active`/`cancelled`), stock restore on cancel inside a transaction.
- **Soft-delete** — replace `is_active` with `deleted_at` so product history remains intact and auditable.
- **Per-line discount** — on top of the invoice-level discount, useful for negotiated pricing.
- **Background CSV export** — for large datasets, queue the report and email the file instead of streaming.
- **Password reset flow** — token-based, stored in a `password_resets` table with expiry.
- **User management UI** — CRUD for users including warehouse assignment, currently seed-only.