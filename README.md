# Stoneholl Latest Internal Comment

A free MIT, read-only Magento Admin order-view helper. It highlights the latest qualifying native order history
comment near the top of an authorized order view, with a bounded plain-text excerpt and explicit search coverage.

## What qualifies

Only `sales_order_status_history` rows for the current order with case-sensitive `entity_name = order`,
`is_visible_on_front = 0`, `is_customer_notified = 0` and text beyond ASCII space, tab, CR and LF qualify.
Null flags, notification `1` or `2`, ambiguous entities, invoice/shipment/creditmemo histories, blank/status-only rows,
gift messages and third-party note stores are excluded. Whitespace rules intentionally do not remove Unicode spaces.

The highest eligible history `entity_id` wins among the newest 200 entries, regardless of `created_at`. Imported or
backdated timestamps do not change record sequence. The source timestamp uses Magento's configured display timezone.
The module does not infer an administrator author: integrations may also write qualifying history.

## Presentation and limits

- Two additional history queries maximum: 201 IDs to detect coverage, then one eligible row within the newest 200 IDs.
- The qualifying comment loads with the authorized Admin page, up to the native 65,535-byte TEXT budget.
  The visible preview contains at most 200 Unicode characters, followed by an ellipsis for longer text.
- A bordered, shaded, read-only region separates the comment from metadata. Line breaks are preserved, long words wrap,
  and expanded long text scrolls inside a keyboard-focusable region.
- `Expand` and `Collapse` switch between the already loaded text and preview immediately. They make no HTTP request,
  perform no additional SQL and require no loading state. Full escaped text is intentionally present in the authorized
  page HTML while collapsed.
- Short comments need no disclosure control. If a customized column exceeds 65,535 bytes, only a 201-character prefix
  is read, no full payload is sent by this helper, and native history remains the route to the full value.
- Literal HTML, image tags, URLs and data URIs remain text in both states. No image downloads, rich text or automatic
  hyperlinks are introduced. Native comments have no upload field; third-party attachments need a separate integration.
- If a truncated initial preview is whitespace-only, offer expansion without choosing an older comment.
- Coverage says either `Searched all N history entries` or `Searched the latest 200 history entries`. An empty result
  makes no claim about older entries outside that window.
- `View order history` scrolls to Magento's existing `order_history_block`; native history stays in place.
- `As of page load` applies to both preview and full text. Expand does not reread history or reauthorize an already
  loaded page. Native AJAX submission, changed flags or changed permissions require a full page refresh to be reflected.
- `Refresh order page` includes only the loaded order ID and native secret key, discarding unrelated request parameters.
  It uses regular Admin navigation and its existing protections. Save unsaved work first.
- Eligibility is rechecked in the second query; rows changed/deleted during selection are excluded.

No comment editing, templates, notifications, author attribution, priority flags, grid columns, exports, polling,
storefront/API additions, expansion endpoint or order mutation are included. Native history actions remain native.

## Installation and configuration

Install the reviewed Composer package with its mandatory free offline Core dependency:

```bash
composer require stoneholl/magento2-latest-internal-comment
bin/magento module:enable Stoneholl_Core Stoneholl_LatestInternalComment
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy
bin/magento cache:clean
```

Use the tagged release and its compatibility records when selecting a package version.
Follow deployment-mode and locale requirements for the target installation. For updates, clear previously generated
package assets through your normal deployment procedure before redeploying static content.

In **Stores > Configuration > Stoneholl > Latest Internal Comment**, enable the helper at **Default Config** scope.
It defaults to **No**. Website/store overrides are unsupported and rejected when saving through the field backend.

Grant BOTH `Magento_Sales::actions_view` and `Stoneholl_LatestInternalComment::view` to view the summary.
`Stoneholl_LatestInternalComment::config` allows configuration only. `Magento_Sales::comment` is not required to read
this summary and still governs native comment submission. Disabled or unauthorized summaries perform no feature query
and render no hidden comment payload.

The summary ACL does not hide Magento's existing order history. This is not a separate private-note security system.
With third-party order visibility restrictions, enable only after verifying authorization and query integration for
those restrictions. Native Open Source action permission does not establish per-order ownership or store isolation.

## Privacy and dependencies

Comment content is read only for the authorized current order. No new tables, indexes, patches, operational storage,
cron or queues are introduced. The full selected comment is intentionally included as escaped text in the authorized Admin page DOM; collapse is
a presentation control. No comment content is copied to persistent module cache, sessions, browser storage APIs, logs
or Connector. Normal native Admin response/cache behavior applies.
Only module settings persist in Magento configuration. Failures show `Temporarily unavailable` and log a fixed
`STONEHOLL_LATEST_INTERNAL_COMMENT_*` diagnostic code without order identifiers, SQL, exceptions or text.

Stored visibility/notification flags do not prove the text has never been shared through another channel. Magento's
existing history remains subject to its native APIs, permissions and third-party extensions; this module does not
change those surfaces or promise to conceal native history from them.

`Stoneholl_Core ^1.0` is mandatory and offline. Connector is optional; no pairing, license check, entitlement or network
connection is needed. This package depends on no other order helper or paid workflow.

## Compatibility evidence

Local validation on 2026-10-19 used these exact pairs with the published offline Core 1.0.0 runtime:

| Edition | Magento | PHP | Database | Evidence |
| --- | --- | --- | --- | --- |
| Open Source | 2.3.7-p4 | 7.4.33 | MySQL 5.7.44 | Native install/upgrade, DI, Admin assets, role/HTTP and browser checks |
| Open Source | 2.4.9 | 8.5.9 | MariaDB 12.3.2 | Native install/upgrade, DI, Admin assets, role/HTTP and browser checks |

The initial review passed 28 native service/config/template checks and 49 persisted-role/HTTP checks on each pair.
The 2026-10-20 preload update repeats native view/role, SQL and browser checks against the final local-toggle behavior.
The intermediate HTTP expansion endpoint was removed; its exploration results do not describe the packaged behavior. All four independent
helpers in the initial review also passed a nine-check combination test on each pair, including block order and unchanged native history.
Browser checks covered native history navigation, page refresh and AJAX comment submission without automatic summary
refresh. The card wraps on narrow screens; Magento's existing Admin minimum page width and horizontal scrolling remain.
Normal navigation does not add an unsaved-draft warning where native Magento provides none. Save draft comments first.

The earlier excerpt-only review used a synthetic local fixture of more than 1,000,000 orders, sparse stores, 950,000 orders for one customer and 100,000
history entries for one order. In that baseline, 100 warm helper calls had p95 of 1.08 ms on the legacy pair and 2.60 ms on the modern
pair, using two history queries each. The first helper call in a fresh CLI process was 6.27 ms and 11.43 ms respectively.
The final preload query has separate 100,000-history SQL checks; the earlier numbers do not benchmark a maximum-size
preloaded comment. Database buffer pools were retained to avoid disrupting shared Docker services; these are not cold-database or
whole-page timing guarantees. Hardware: AMD Ryzen 5 5600H, 6 cores / 12 threads, approximately 15 GiB RAM.

The 2026-10-20 preload update repeats PHP 7.4/8.0/8.5 parsing, unit, XML/XSD, coding-standard and native Admin/SQL
checks. Browser checks cover instant local expansion/collapse, literal image markup and narrow-screen scrolling.
The earlier AJAX-expansion exploration was superseded before packaging: no expansion route/controller remains.
Exact runtime-tested pairs are also recorded in the release compatibility.json asset.

Source compatibility targets Magento Open Source 2.3.7-p4 through 2.4.9 and PHP 7.4 through 8.5, including 8.0.
Other installable pairs remain declared targets. No PHP 8.0 Magento runtime pair, Adobe Commerce, third-party Admin
layout or arbitrary Magento/PHP range is certified. Composer constraints do not establish runtime compatibility.

Native validation used local app/code copies and existing dependencies. Repository CI checks syntax and metadata;
package download verification does not establish a fresh full dependency installation or additional runtime pairs.

## Extension surface and removal

Layout block `stoneholl.latest_internal_comment`, `sales_order_view` placement and
`stoneholl_latest_internal_comment/general/enabled` are documented customization points. Internal readers/presentation
classes are not public APIs; a future grid/bulk consumer needs its own query and disclosure design.

Disable `Stoneholl_LatestInternalComment`, remove its Composer package, and run the deployment commands appropriate to
your installation. Remove only its `stoneholl_latest_internal_comment/` configuration rows through your established
configuration management procedure. Native orders/history are untouched. Keep Core while another module depends on it.

## Composer package naming

The canonical Composer name is `stoneholl/magento2-latest-internal-comment`. The former `stoneholl/module-latest-internal-comment` name is a legacy identity. The renamed package replaces that identity at `self.version`, so Composer installs one copy of the Magento module. Existing immutable releases remain unchanged.

When migrating an existing root requirement, back up the project and remove the old requirement without updating, then require the new package with its dependencies:

```bash
composer remove stoneholl/module-latest-internal-comment --no-update
composer require stoneholl/magento2-latest-internal-comment --with-all-dependencies
```

Run the Magento upgrade, compilation and cache commands documented above according to your deployment mode. Keep native `Stoneholl_*` module names and configuration paths unchanged.
