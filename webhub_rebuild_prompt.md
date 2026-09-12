# WEBHUB.UZ — FULL REBUILD SPECIFICATION FOR AI CODING AGENTS

You are rebuilding **webhub.uz**, a live IT-services agency website (founder: G'iyosiddin Adhamjonov, based in Fergana, Uzbekistan). This document is the single source of truth for the rebuild. Follow it exactly. Where this document gives a rule and your own judgment disagrees, this document wins — flag the disagreement in your output instead of silently deviating.

---

## 0. MANDATORY WORKFLOW

Do not jump straight to writing code. Execute these six phases **in order**, and produce the stated output for each phase before moving to the next one.

1. **AUDIT** — Read every section of this spec. Produce a written checklist of every requirement in it (design, architecture, admin features, user features, auth, API, installer, database, security). This checklist is your master task list for the rest of the build.
2. **PLAN** — Produce a file/folder structure, a database schema (based on Section 10, expanded as needed), and an implementation order (based on Section 1's build order) before writing any feature code.
3. **IMPLEMENT** — Build in the order defined in Section 1. Do not skip ahead to a later phase while an earlier phase has unresolved TODOs.
4. **TEST** — For every item in your Phase 1 checklist, verify it against the Acceptance Criteria given for that item in this document (most sections below include explicit, checkable criteria). Record pass/fail per item.
5. **FIX** — Resolve every failed item from Phase 4. Do not mark the project complete with known failing items.
6. **FINAL AUDIT** — Re-run the full checklist from Phase 1 end-to-end against the live build (not against your memory of having built it). Confirm: nothing from the original spec was dropped, nothing outside the spec was silently added, and Section 2's non-regression rules hold. Only after this passes is the project done.

---

## 1. PROJECT CONTEXT, BUSINESS GOALS, AND BUILD ORDER

### 1.1 What this project is
WebHub.uz is a Fergana-based IT services agency site. Clients browse services (websites, Telegram bots, AI solutions, mobile apps), view a portfolio, and submit orders/applications. The founder communicates with clients directly through the platform.

### 1.2 Business goals that MUST be preserved (non-negotiable)
- Present a services catalog with pricing.
- Capture client orders/applications ("ariza").
- Showcase completed work (portfolio) to build trust.
- Enable direct one-to-one communication between the admin and each client.

Any implementation choice that weakens one of these four goals is a defect, regardless of how good it looks.

### 1.3 Known defects in the current live site — MUST be fixed in the rebuild
These were found by auditing the current live version. Treat each as a required bug fix, not an optional nice-to-have:
- The homepage stat counters ("Projects+", "Clients+") currently render as `0`. **Fix:** these values must be either (a) editable by the admin in the CMS, or (b) computed live from the database (e.g., `COUNT()` of portfolio items / clients) — implementer's choice, but they must never show a static, uneditable `0`.
- The Portfolio page currently has zero projects listed. **Fix:** the admin panel must let the admin add portfolio entries, and the rebuilt Portfolio page must display them; ship with the CMS empty-state handled gracefully (no broken layout when a section is empty), but the admin creation flow must be fully functional and tested.
- The footer contains an empty `tel:` link with no phone number. **Fix:** no link in the final build may render with an empty `href` or an empty visible label. Every contact link must come from a single source of truth (`site_settings` table) and either render fully populated or not render at all.

### 1.4 Required build order
Build in this sequence. Do not build Admin or User panel UI before the design system and database exist; do not build the API before the features it exposes exist.

1. Database schema (Section 10) + `install.php` (Section 9)
2. Design system foundation: CSS variable theme (light/dark), glass-effect components, icon set, typography, base animation utilities (Section 4)
3. Public Homepage (Section 5.1)
4. Authentication: Google OAuth for users, separate admin login (Section 6) + login loading animation (Section 7)
5. User Panel (Section 5.3)
6. Admin Panel (Section 5.2)
7. `/api/` layer (Section 8)
8. Security hardening pass (Section 11)
9. Testing & QA (Section 13)
10. Final audit (Section 14)

---

## 2. NON-REGRESSION RULES ("DO NOT BREAK EXISTING FUNCTIONALITY")

These rules apply for the entire duration of the project, at every phase:

1. **No feature deletion without replacement.** If a requirement in this document describes a capability, the shipped build must have that capability working. If you believe a requirement is genuinely impossible or contradictory, do not silently drop it — implement the closest compliant version and explicitly flag the conflict in your output.
2. **No silent scope changes.** Do not substitute a different technology, library, or approach than what Section 3 specifies without flagging it explicitly.
3. **No regressions between phases.** If Phase 6 (Admin Panel) work touches shared code (e.g., the theme system, the chat component, the API), re-run the Phase 4/5 acceptance criteria for the features you touched before moving on.
4. **One source of truth per data type.** Site-wide values (contact info, stats, pricing) must be read from the database in exactly one place and reused everywhere they appear — never hardcoded in multiple templates, which is how the empty `tel:` bug happened originally.
5. **Every new page/component must support both themes and be responsive** before it is considered done — this is not a final "pass" step, it's a per-component requirement.
6. **Uzbek-only text, no exceptions**, including error messages, validation messages, admin-panel labels, email/notification content, and placeholder/empty-state text. Any hardcoded English or Russian string left in the UI is a defect.

---

## 3. TECHNOLOGY STACK (resolved — no ambiguity)

**Required:**
- **Backend:** PHP 8+, no framework (no Laravel, Symfony, Slim, etc.), procedural or lightweight OOP as needed.
- **Frontend markup/styling:** HTML5 + CSS3, hand-written, no CSS framework (no Bootstrap/Tailwind build step).
- **Client-side interactivity:** **Vanilla JavaScript (ES6+), no framework and no external library** (no React/Vue/jQuery/Alpine). This is an explicit, resolved requirement, not an option — see note below.
- **Data interchange:** JSON for all AJAX/API payloads.
- **Database:** MySQL, accessed exclusively via PHP PDO with prepared statements.

**Resolved ambiguity:** An earlier version of this spec listed only "PHP, HTML, CSS, JSON, MySQL" without JavaScript. This was an oversight, not an intentional exclusion — features explicitly required elsewhere in this document (AJAX form submission without page reload, live chat updates, notification badges, theme switching without a flash of unstyled content, scroll-triggered animations) are not achievable in a browser without client-side JavaScript. Vanilla JavaScript is therefore part of the required stack. If truly no JavaScript is wanted, chat and notifications must fall back to plain page-refresh polling — but this is the fallback, not the default, and must be called out explicitly if chosen.

**Explicitly forbidden:**
- No build tools, bundlers, transpilers (no Webpack/Vite/Babel).
- No npm/Composer dependency installation step required to run the site — vendor any needed asset (e.g., icon SVGs) as static files committed to the repo.
- No reliance on external CDNs for core functionality — the site (aside from the Google OAuth network call itself) must run with all assets served locally.

---

## 4. DESIGN SYSTEM

### 4.1 Visual direction
- Target aesthetic: extreme minimalism with a "2100" futuristic feel — generous negative space, oversized confident typography, restrained color palette, no decorative clutter. Reference point for tone: the visual style of **soon.uz** (large type, sparse layout, quiet motion).
- Every visual element must serve a functional or clear hierarchical purpose — decoration for its own sake is a defect.

**Acceptance criteria:**
- [ ] No page has more than one primary visual accent color active at once outside of interactive states (hover/focus).
- [ ] Hero and section headings use significantly larger type scale than body text (establish a documented type scale, e.g., 1.25–1.5 ratio, and use it consistently).

### 4.2 Animation
- Scroll-triggered entrance animations (fade/slide/parallax) for homepage sections.
- Micro-interactions on buttons/cards (hover and touch-active states): subtle transform + shadow change, no jank.
- Page/route transitions must not feel like a hard reload (use a brief fade or equivalent transition even on classic multi-page PHP navigation).

**Acceptance criteria:**
- [ ] All animations respect `prefers-reduced-motion` (reduced or disabled when the OS setting requests it).
- [ ] No animation blocks interaction (a user must be able to click a button before its entrance animation finishes).

### 4.3 Glassmorphism ("Apple glass")
- Cards, navbar, modals, and chat panels use `backdrop-filter: blur(...)`, a semi-transparent background, and a subtle 1px semi-transparent border.
- Must render correctly in both light mode (light/white-based glass) and dark mode (dark-based glass).

**Acceptance criteria:**
- [ ] Glass effect is implemented as a reusable CSS class/utility, not copy-pasted inline styles.
- [ ] Verified visually in both themes, at both mobile and desktop widths, over a busy background (image or gradient) to confirm the blur is actually visible.

### 4.4 Light / dark mode
- Implemented via CSS custom properties, toggled with a `data-theme` attribute (or equivalent) on the root element.
- On first visit, detect `prefers-color-scheme` and apply it as the default.
- User's explicit choice is persisted in `localStorage` and takes priority over the system setting on return visits.
- Theme toggle is present in the navigation on every page, and switching is an instant, smooth transition (no flash of wrong-theme content on page load).

**Acceptance criteria:**
- [ ] Reloading the page preserves the user's last chosen theme.
- [ ] A fresh browser profile with OS set to dark mode loads the site in dark mode by default.
- [ ] Every component built in Sections 5.1–5.3 has been visually checked in both themes.

### 4.5 Iconography
- One consistent, professional icon set (e.g., Lucide or Phosphor), stroke-based, vendored locally as SVG (not loaded from a CDN).
- Icon color/weight controlled via CSS, not baked into the SVG files.

**Acceptance criteria:**
- [ ] No two icons from different icon sets/styles appear in the shipped UI.
- [ ] Icons render correctly with the site offline (aside from the OAuth call itself), confirming they are not CDN-dependent.

### 4.6 Language
- 100% of user-facing text — homepage, admin panel, user panel, error messages, validation messages, notifications, emails, placeholder/empty states — is in Uzbek (Latin script) only. No language switcher, no other language anywhere in the shipped UI.

### 4.7 Responsiveness
- Mobile-first CSS, tested from 360px to 2560px wide.
- Admin and User panels are fully usable on mobile (collapsible/hamburger navigation, minimum 44px touch targets).
- Chat UI on mobile follows a familiar messaging-app pattern (input pinned to bottom, natural scroll behavior).

**Acceptance criteria:**
- [ ] Every page/panel tested at minimum: 360px, 768px, 1024px, 1440px, 2560px widths.
- [ ] No horizontal scroll or overlapping elements at any tested width.

---

## 5. SITE ARCHITECTURE — 3 SECTIONS

### 5.1 `/` — Public Homepage
Required sections, top to bottom:
1. Hero: headline, short description, CTA buttons, live stats (see 1.3 fix).
2. Services: cards for Website / Telegram Bot / AI Solution / Mobile App, pricing editable by admin.
3. Portfolio preview: latest 4–6 admin-added projects (image, short description, external link).
4. Blog preview: latest posts.
5. "How we work" — the 4-step process, shown as an animated timeline.
6. Contact/CTA section with a form (name, phone, service type, message) that saves to the database and appears in the admin panel as a new application ("ariza").
7. Footer: fully populated contact links only (see 1.3 fix; see rule 2.4).

**Acceptance criteria:**
- [ ] Submitting the contact form creates exactly one new row in the applications table and is visible in the admin panel without a page refresh needed on the admin side beyond normal polling/reload.
- [ ] Stat counters never display a hardcoded `0`.
- [ ] Portfolio preview and Blog preview both handle the "zero items" case without breaking layout, and display correctly once the admin adds items.

### 5.2 `/admin/` — Admin Panel (protected, admin-only)
The admin must be able to fully manage:

1. **Homepage content** — hero text, stat numbers (manual override or "recompute from database" action), "how we work" step text.
2. **Media library** — upload, replace, delete logo, banner, OG image, and any other image/video asset, via a drag-and-drop upload UI.
3. **Services & pricing** — create/edit/delete services, price, description, "what's included" list.
4. **Portfolio management** — add projects (image, title, description, client name, link, category). This directly fixes the empty-portfolio defect (1.3).
5. **Blog management** — write/edit posts (simple WYSIWYG or Markdown editor), image insertion, draft vs. published state.
6. **User management** — list all registered users (name, email, linked Google account, registration date, active/blocked status); open any user's profile to see their full application and message history.
7. **Chat (Admin ↔ User)** — a dedicated conversation thread per user; text messages plus file/document/image attachments with download links; read/unread state; near-real-time updates via AJAX polling (every 3–5 seconds).
8. **Applications ("ariza") management** — view all applications from both the homepage form and the User Panel; change status through a defined lifecycle: `new → in review → approved → completed → cancelled`.
9. **Notifications** — send a broadcast or a targeted notification to a user (e.g., "Your application was approved").
10. **Site settings** — SEO fields (meta description, keywords), contact info (phone, Telegram, Instagram) as the single source of truth used everywhere on the public site, theme color values.
11. **Admin authentication** — separate login/password screen (bcrypt-hashed password), not via Google — see Section 6.

**Acceptance criteria (per capability above):**
- [ ] Each of the 11 capabilities has a corresponding UI screen, and a create/edit/delete (as applicable) action that persists to the database and is reflected on the public site or user panel without manual cache-clearing.
- [ ] Admin panel is fully operable on a mobile viewport (see 4.7).
- [ ] Every admin action that changes data is protected by a CSRF token (see Section 11).

### 5.3 `/user/` — User Panel (for registered clients)
Required screens:
1. **My Profile** — name, phone, avatar, linked Google account info, any user-editable settings.
2. **Services** — browse the catalog, customize a selection (based on admin-defined options/add-ons), and submit it as a new application.
3. **My Applications** — list of all submitted applications with current status and history.
4. **Chat** — direct messaging with the admin, with file attachment support; can be scoped per-application or as one general thread (implementer's choice, but must be consistent and documented).
5. **Notifications** — feed of admin-sent updates and application status changes, with an unread-count indicator.
6. **News/Blog** — read published blog posts.

**Acceptance criteria:**
- [ ] A user can go from "browse services" to "submitted application visible in their own My Applications list and in the admin's Applications list" in one uninterrupted flow, with no page-reload-losing-state errors.
- [ ] Unread notification count updates without a full page reload after the admin sends a notification (within the polling interval).
- [ ] Fully usable on mobile (see 4.7).

---

## 6. AUTHENTICATION

- **Users:** Google OAuth 2.0 only. No separate email/password registration path for regular users. **"No verification codes" means:** no additional custom OTP/SMS/email confirmation step is added on top of Google's own sign-in flow — Google's authentication is treated as sufficient identity verification. This does not mean skipping OAuth security itself (state parameter, token validation, etc. are still required).
- On first Google sign-in, auto-create the user record (name/email from Google); prompt for a phone number afterward (in Profile) since Google doesn't reliably provide one.
- **Admin:** separate login screen with login + password (bcrypt hash), intentionally not tied to Google, so the admin account isn't dependent on a third-party identity provider.
- Session management: native PHP sessions with `HttpOnly`, `Secure`, and `SameSite=Strict` cookie flags.

**Acceptance criteria:**
- [ ] A new Google account can complete first-time sign-in with zero manual admin intervention and zero additional verification step.
- [ ] Admin login is fully independent of Google — Google being down does not block admin access.
- [ ] Session cookies inspected in browser dev tools show `HttpOnly`, `Secure`, `SameSite=Strict`.

---

## 7. LOGIN LOADING ANIMATION

- After a successful Google sign-in and before the user lands on their dashboard, show a full-screen loading animation lasting **3–5 seconds**.
- Centered content: the WebHub logo/icon performing a subtle animation (rotation, pulse, or SVG stroke draw-on), over a glassmorphism background, consistent with the theme system in Section 4.
- This time window must be used productively: prefetch the user's profile and applications in the background so the dashboard renders fully populated the instant the animation ends — it must be a real loading state, not an artificial delay with nothing happening behind it.

**Acceptance criteria:**
- [ ] Animation duration measured at 3–5 seconds across at least 3 test runs.
- [ ] Dashboard data is already fetched (verified via network tab) before the animation ends, i.e. no additional loading spinner appears immediately after.

---

## 8. `/api/` — EXTERNAL API (FOR THE MOBILE APK)

- A JSON REST API exposing everything the public site and the authenticated user's data provide, so a companion Android/iOS app can consume the entire platform.
- All requests/responses use `Content-Type: application/json`.
- Auth: after Google sign-in, the server issues a bearer API token; the mobile app sends it as `Authorization: Bearer <token>` on every authenticated request.

**Minimum required endpoints:**
```
GET  /api/services              — list of services
GET  /api/portfolio             — portfolio projects
GET  /api/blog                  — blog posts
GET  /api/settings              — public site settings (contact info, social links)

POST /api/auth/google            — exchange a Google token for a WebHub API token (creates the user on first use)
GET  /api/user/profile           — current user's profile
PUT  /api/user/profile           — update profile

GET  /api/user/applications      — current user's applications
POST /api/user/applications      — submit a new application

GET  /api/chat/{thread_id}       — messages in a thread
POST /api/chat/{thread_id}       — send a message (text and/or file)

GET  /api/notifications          — list notifications
POST /api/notifications/read     — mark as read
```

- Error responses use a consistent shape: `{ "success": false, "error": "message" }`.
- Rate limiting (e.g., per-IP or per-token request cap per minute) must be implemented on the API — this was flagged as easy to forget and is not optional.

**Acceptance criteria:**
- [ ] Every endpoint above returns valid JSON for both success and error cases.
- [ ] An unauthenticated request to any `/api/user/*` or `/api/chat/*` or `/api/notifications*` endpoint returns a 401-equivalent JSON error, never data.
- [ ] Rate limiting is demonstrably active (a rapid burst of requests gets throttled).

---

## 9. `install.php` — INSTALLER (REQUIRED)

Must exist at the project root and walk through, as a step-by-step wizard:

1. Check server requirements (PHP version, required extensions: `pdo_mysql`, `gd`/`fileinfo`, etc.).
2. Collect MySQL connection details (host, database name, user, password) and test the connection.
3. Auto-create all required tables (`CREATE TABLE IF NOT EXISTS ...`, matching Section 10).
4. Create the first admin account via a form (login + password).
5. Collect Google OAuth Client ID/Secret.
6. Auto-generate the `config.php` file from the collected values.
7. **Lock itself after successful install** — e.g., write an `install.lock` file and refuse to run again once it exists, or self-delete.

**Acceptance criteria:**
- [ ] Running `install.php` on a clean database produces a fully working site with one admin account, with zero manual SQL required.
- [ ] Attempting to visit `install.php` again after a successful install is blocked.

---

## 10. DATABASE SCHEMA (baseline — expand as needed during PLAN phase, do not remove fields)

```
users            (id, google_id, name, email, phone, avatar, status, created_at)
admins           (id, login, password_hash, name, created_at)
services         (id, title, description, price, features_json, sort_order)
portfolio        (id, title, description, image, client_name, link, category, sort_order)
blog_posts       (id, title, body, image, status, created_at)
applications     (id, user_id, service_id, description, status, created_at)
chat_threads     (id, user_id, created_at)
chat_messages    (id, thread_id, sender_type, sender_id, message, file_path, is_read, created_at)
notifications    (id, user_id, title, message, is_read, created_at)
site_settings    (key, value)
api_tokens       (id, user_id, token, created_at, expires_at)
```

**Acceptance criteria:**
- [ ] Every field listed above exists in the final schema (additional fields/tables are fine; removing these is not).
- [ ] All foreign keys (`user_id`, `thread_id`, `service_id`, etc.) are enforced at the database level, not just in application code.

---

## 11. SECURITY REQUIREMENTS

1. All SQL queries use PDO prepared statements — no string-concatenated SQL, anywhere.
2. All user-supplied content is escaped with `htmlspecialchars()` (or equivalent context-appropriate escaping) on output.
3. CSRF tokens on every state-changing form and AJAX request.
4. File uploads: MIME-type validation, file size limits, an explicit allowed-extensions list, and storage outside the web root (or in a directory configured to never execute scripts).
5. Brute-force protection (attempt limiting) on admin login and on the API.
6. `config.php` (and any credentials file) is inaccessible from the web (via server config, e.g. `.htaccess` deny rule, or by placing it outside the public web root).

**Acceptance criteria:**
- [ ] A basic SQL injection attempt against any form field fails safely.
- [ ] A basic XSS payload submitted through any text field (chat message, application description, blog comment if present) renders as inert text, not executed script.
- [ ] Repeated rapid-fire admin login attempts get throttled/blocked.
- [ ] Requesting `config.php` directly via URL returns a 403/404, not the file contents.

---

## 12. ADDITIONAL REQUIREMENTS (previously flagged as easy to forget — still required)

- **SEO preserved and editable:** meta tags, Open Graph tags, `robots.txt`, `sitemap.xml`, all carried over from the current site and editable from the admin panel.
- **Performance:** lazy-loaded images, WebP conversion where the server supports it, minified CSS/JS (manually or via a simple PHP-based minifier — no external build tool per Section 3).
- **Custom 404 and error pages**, styled consistently with the rest of the site, with useful links back into the site.
- **Privacy Policy and Terms of Use pages** (at minimum a reasonable baseline template) — a minimal legal basis expected alongside Google sign-in.
- **Backup:** an admin-panel action to export the database (SQL or JSON) on demand.
- **Audit log:** a simple table logging admin actions (who, when, what changed).

---

## 13. TESTING & QA CHECKLIST (execute in the TEST phase of Section 0)

- [ ] All Section 1.3 known-defect fixes verified individually (stats never show hardcoded 0; portfolio add-flow works; no empty `tel:` links anywhere).
- [ ] All Section 4 design acceptance criteria pass in both themes and across all breakpoints.
- [ ] All Section 5.1–5.3 acceptance criteria pass.
- [ ] Section 6 auth flows tested for both a brand-new Google account and a returning one, plus admin login independent of Google.
- [ ] Section 7 login animation timing and prefetch behavior verified.
- [ ] Section 8 API endpoints tested with valid token, missing token, and expired/invalid token.
- [ ] Section 9 installer tested on a genuinely clean database and confirmed to lock itself afterward.
- [ ] Section 11 security checks (SQLi, XSS, CSRF, brute-force, config exposure) each individually attempted and confirmed blocked.
- [ ] Every string visible in the UI reviewed and confirmed to be Uzbek-language (Section 2, rule 6).

---

## 14. FINAL AUDIT (Section 0, Phase 6)

Before declaring the project complete, reconfirm all of the following against the actual running build:
- [ ] Every requirement in Sections 1–12 is implemented and passes its stated acceptance criteria.
- [ ] The four business goals in Section 1.2 are all functioning end-to-end.
- [ ] No requirement from the original scope was dropped, weakened, or silently reinterpreted.
- [ ] No functionality outside this spec was added that isn't clearly beneficial and non-conflicting (flag anything you added beyond spec, and why).
- [ ] All items in Section 13 are checked off with no open failures.

---

## 15. OUT OF SCOPE (do not do these — for clarity, not because they were requested and denied)

- No additional languages or a language switcher.
- No JS/CSS frameworks, no build pipeline, no npm/Composer runtime dependency for the site to function.
- No changes to the four core business goals (Section 1.2) or the overall business model.
- No third-party analytics/ad scripts unless explicitly added later by the site owner — not part of this build.
