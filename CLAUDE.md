# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Run all tests with coverage
composer test:phpunit

# Run a single test file
composer test:phpunit -- --filter=MountServiceTest

# Run static analysis (PHPStan level 9)
composer analyse:phpstan

# Fix code style
composer cs-fix

# Check code style without fixing
composer analyse:phpcsfixer

# Run all analysis tools (lint, phpstan, cs-fixer, compatibility)
composer analyse

# Run mutation testing
composer test:infection
```

## Architecture

This is a Symfony bundle (`atoolo/microsite-bundle`) that enables a "microsite" pattern: content from a main site is mounted and URL-rewritten at specific paths within a subdomain/microsite host.

**Core flow:**
1. `MicrositeContextFactory` reads environment variables (`ATOOLO_MICROSITE_HOST`, `ATOOLO_MICROSITE_PATH`, `ATOOLO_MAIN_HOST`) from the current request to build a `MicrositeContext`.
2. `MicrositeContext` holds configuration: which host/path is the microsite, which is the main host, which resource types are mountable, and the current path.
3. `MountService` determines whether a given resource path should be shown (mounted) in the microsite by checking: object type eligibility, navigation tree membership, and avoiding double-mounting.
4. `MicrositeUrlRewriteHandler` (implements `UrlRewriterHandler`) rewrites URLs: microsite paths strip the micrositePath prefix; non-mountable resources redirect to mainHost; mountable ones get the mount transformation applied.
5. `MicrositeCanonicalHostProvider` (implements `CanonicalHostProvider`) returns the microsite host for canonical security headers.

**External dependencies:**
- `atoolo/resource-bundle` — resource loading (`ResourceLoader`, `LangPathService`)
- `atoolo/rewrite-bundle` — URL rewriting infrastructure (`UrlRewriterHandler`)
- `atoolo/security-bundle` — canonical host interface (`CanonicalHostProvider`)

**Service registration:** `config/services.yaml` wires all factories and services. `MicrositeContextFactory` and `MountServiceFactory` are the entry points; factories return `null` when no microsite context is configured, so all dependent services are optional.

## Testing Patterns

Tests mirror the `src/` directory under `test/`. Use `TestResourceFactory` (in `test/`) to create test `Resource` instances.

- Mock dependencies with `createMock()` / `createStub()`
- One assertion per test method
- Every assertion must include a descriptive message as the last argument
