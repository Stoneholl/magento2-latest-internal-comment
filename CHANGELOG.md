# Changelog


## 1.0.1 - 2026-10-23

- Rename the Composer package to `stoneholl/magento2-latest-internal-comment` and replace its corresponding legacy `stoneholl/module-latest-internal-comment` identity at `self.version`.
- Require the canonical Core package from version 1.0.1 and update installation/migration documentation.
- Keep Magento module identifiers, configuration, storage and licensing unchanged.

## 1.0.0 - 2026-10-20

- Frame the read-only comment and show a 200-character preview with native-style Expand/Collapse controls.
- Preload the authorized, size-bounded full comment with the page; Expand/Collapse toggle locally without a request.
- Keep images and HTML literal text; do not add uploads, remote image loading or attachment integration.

- Add a default-disabled, read-only Admin summary of the latest qualifying native order history comment.
- Bound reads to 200 candidates and one native-size comment, with explicit search coverage and a 200-character preview.
- Require separate summary and native order-view permissions before reading history.
- Register the free offline product with mandatory Core and optional Connector.

- Correct the helper name in Default-scope validation errors.
- Keep the card readable on narrow Admin viewports and preserve long-word wrapping.
- Validate the two exact Magento/PHP pairs documented in README, including native ACL/AJAX and million-order fixtures.

- Restrict page-refresh links to the authorized loaded order ID and native Admin key; omit unrelated request parameters.

Runtime-tested compatibility is limited to the exact Magento/PHP pairs documented in README.