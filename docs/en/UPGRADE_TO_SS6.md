# Silverstripe CMS 6 Upgrade Guide

This document outlines the key changes and actions required to upgrade your project to be compatible with Silverstripe CMS 6.

## ⚠️ BREAKING CHANGE: Core Dependencies

The project now requires Silverstripe CMS 6 and has updated its dependencies accordingly.

- **`silverstripe/recipe-cms`**: Upgraded to `^6.0`.
- **`sunnysideup/ecommerce`**: Upgraded to `^33.0`.

You must update your project's `composer.json` to reflect these new requirements.

## 🚨 CRITICAL REVIEW REQUIRED / RISKY: Incomplete Dependency Update

The upgrade for a key dependency has been deferred, which may lead to instability or compatibility issues.

- **`sunnysideup/ecommerce_discount_coupon`**: This dependency is currently set to `^5.0-dev` because no compatible stable release for Silverstripe 6 was available at the time of this upgrade.

**You must manually review this dependency and update it to a stable, Silverstripe 6 compatible version as soon as one becomes available.**

## API Changes

The following changes have been made to the codebase to align with Silverstripe 6 standards.

- **`QuickCouponAdmin.php`**: The `getEditForm` method now uses the `#[Override]` attribute to indicate it is overriding a parent method. This is a new requirement in Silverstripe 6.
- **`GridFieldCreateCouponFromInternalItemIDButton.php`**: The `use SilverStripe\View\ArrayData;` statement has been removed as it was redundant. The `SilverStripe\Model\ArrayData` import is now used instead. This is a minor cleanup and should not require any changes on your part.
