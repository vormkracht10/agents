---
title: Filament v4 Guidelines
description: Comprehensive rules and best practices for building applications with Filament v4, covering project structure, resources, forms, tables, actions, authorization, and testing standards.
---

# Filament v4 Guidelines

These rules define the standards and best practices for building applications with **Filament v4**.  
Follow them strictly to ensure consistency, maintainability, and scalability.

---

## 1. General Principles

- Target **Filament v4 only**. Do not include v2/v3 patterns.
- Prefer clarity and predictability over cleverness.
- Keep logic out of the UI layer as much as possible.
- Follow Laravel and Filament naming conventions at all times.
- Use PHP 8.2+ features where appropriate (readonly, enums, typed properties).

---

## 2. Project Structure

- Use Filament’s default directory structure.
- Keep each resource, page, widget, and action in its own file.
- Do not overload resources with unrelated logic.
- Shared logic must live in:
  - Actions
  - Services
  - Support classes
  - Domain-specific classes

---

## 3. Resources

- One **Eloquent model = one Filament Resource**.
- Resources must be thin:
  - No business logic
  - No heavy data transformations
- Use:
  - `form()` for input definition only
  - `table()` for column and filter definitions only
- Resources may **only reference actions**, never define them inline.

---

## 4. Forms

- Use `Forms\Components` exclusively.
- Always:
  - Define labels explicitly
  - Define validation rules explicitly
- Group fields using:
  - `Section`
  - `Fieldset`
  - `Tabs`
- Avoid inline closures for complex logic.
- Use:
  - Form Actions for side effects
  - Enums for select options where possible

---

## 5. Tables

- Use `Tables\Columns` explicitly.
- Columns must:
  - Be sortable only when indexed
  - Be searchable only when meaningful
- Prefer:
  - `TextColumn` with formatters
  - `BadgeColumn` for statuses
- Filters and table actions **must be referenced**, not defined inline, when non-trivial or reusable.

---

## 6. Actions (MANDATORY RULE)

- **Every Filament Action MUST be defined in its own stand-alone class file.**
- ❌ Defining actions inline inside:
  - Resources
  - Pages
  - Widgets
  - Tables
  - Forms  
  is strictly forbidden.
- Actions must:
  - Extend the appropriate Filament Action base class
  - Be single-purpose
  - Contain all execution logic internally
- Actions must be referenced like:
  - `MyCustomAction::make()`
- Reusable actions must live in a shared namespace (e.g. `App\Filament\Actions`).

---

## 7. Authorization

- Every action must define its own authorization logic.
- Always use:
  - Policies
  - Filament authorization hooks
- Never rely on UI-only restrictions.
- Actions must fail safely when unauthorized.

---

## 8. State & Data Handling

- Avoid querying the database inside closures.
- Use eager loading consistently.
- Cache expensive lookups where appropriate.
- Never mutate model state directly from UI components.
- All mutations must happen inside Actions or domain services.

---

## 9. Styling & UI

- Use Filament defaults first.
- Avoid custom CSS unless strictly necessary.
- Prefer components over view overrides.
- Dark mode must be supported by default.

---

## 10. Performance

- Paginate all tables.
- Limit relation counts and computed columns.
- Use `->lazy()` and `->defer()` where applicable.
- Avoid N+1 queries at all costs.

---

## 11. Testing

- All actions must have:
  - Authorization tests
  - Execution tests
- Resources must have:
  - Authorization tests
  - Form validation tests
- Complex actions require feature tests.
- Use Pest where possible.

---

## 12. Documentation & Comments

- Code must be self-explanatory.
- Comments are only allowed for:
  - Non-obvious constraints
  - Workarounds
- Public-facing actions must be documented.

---

## 13. Forbidden Practices

- ❌ Inline action definitions
- ❌ Business logic in Resources
- ❌ Inline SQL queries
- ❌ Magic strings for states or roles
- ❌ Copy-pasting components without abstraction
- ❌ Ignoring policies

---

## 14. Review Checklist

Before merging:
- [ ] All actions are stand-alone classes
- [ ] No inline action definitions
- [ ] Policies applied and tested
- [ ] No business logic in Filament classes
- [ ] No duplicated UI logic
- [ ] Performance impact reviewed

---

## 15. Testing

Always test using Pest tests.

# 16. If working in Filament v4

If you are working Filament v4, you must know that 

**These rules are mandatory.  
Violations must be rejected during review.**
