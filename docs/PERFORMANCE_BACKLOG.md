# SchoolCanteen Performance & Reliability Backlog

Status legend: `[ ]` pending, `[~]` partial/in progress, `[x]` closed or intentionally retained.

## P0 — Security, auth, data integrity

- [x] Remove real credentials and duplicate config from `.env.example`.
- [~] Remove Supabase Auth from the hot path. Phase 1 adds short-lived verified-user caching, request coalescing, and hard HTTP timeouts. Final target: local asymmetric JWT/JWKS verification.
- [ ] Rotate any database password that was previously committed/shared.
- [ ] Reconcile repository migrations with the database dump.
- [x] Revalidate expired pickup slots inside the checkout transaction.
- [ ] Unify modifier availability rules into one backend source of truth.
- [ ] Expose merchant/product `can_order` state consistently.
- [x] Enforce one merchant per owner with a database unique constraint.
- [ ] Harden pickup-code generation against collision/race conditions.

## P1 — Request count, API contract, database

- [ ] Preserve pagination metadata in the frontend API client.
- [ ] Remove fetch-all-pages loops from admin/merchant pages.
- [ ] Move list statistics to aggregate backend endpoints.
- [ ] Add server-driven pagination/search/filter to the public catalog.
- [ ] Remove mock-data adapters from production admin screens.
- [ ] Batch cart product + modifier resolution to avoid N+1 requests.
- [ ] Reduce `router.refresh()` usage; invalidate/update focused query caches.
- [ ] Benchmark and remove unnecessary checkout locks.
- [ ] Add query indexes based on real `EXPLAIN ANALYZE` results.
- [ ] Replace index-unfriendly `whereDate()` filters with timestamp ranges.
- [ ] Add endpoint-specific rate limits.
- [x] Add explicit timeout handling to the current Supabase Auth HTTP call.

## P2 — Frontend runtime and caching

- [ ] Cache public catalog/home data with a short revalidation window.
- [ ] Reduce unnecessary client-only boundaries on public pages.
- [ ] Scope Cart/Query providers to route groups that need them.
- [ ] Remove unnecessary public-to-student redirect hops.
- [ ] Re-evaluate product-link prefetch after API latency is fixed.
- [ ] Add responsive image sizing and a consistent image pipeline.
- [ ] Move slow media work out of synchronous request paths where possible.
- [ ] Split/lazy-load rare heavy features such as QR/modifier editors.
- [ ] Put search/filter state in URL and make the server authoritative.
- [ ] Optimize client-side product filtering if any large in-memory lists remain.

## P3 — Scale validation, tests, UX later

- [ ] Load-test 1/10/50/100/200 concurrent checkouts.
- [ ] Verify merchant-wallet contention under burst traffic.
- [ ] Add E2E coverage for the full order → pickup → escrow flow.
- [ ] Re-run a clean frontend production build with all dependencies available.
- [ ] Add admin loading skeletons after backend latency work.
- [ ] Reduce expensive mobile visual effects after core performance work.
- [ ] Improve accessibility labels/lang metadata after core performance work.
- [ ] Replace native confirm dialogs after core performance work.

## Existing behavior to retain

- [x] Backend revalidates prices and stock at checkout.
- [x] Checkout uses a database transaction and deterministic product locks.
- [x] Wallet/escrow consistency protections are retained.
- [x] Midtrans signature/idempotency protections are retained.
- [x] Current dump is small; optimization should focus on network/request architecture first.
- [x] Current snapshot data integrity did not show an obvious corruption pattern.
