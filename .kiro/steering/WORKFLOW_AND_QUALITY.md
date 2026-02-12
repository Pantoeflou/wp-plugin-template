# Workflow & Quality Standards

This document defines how tasks must be executed.

---

# Task Size Rule

A task must:

- Modify a small number of files.
- Solve one concern.
- Avoid large structural changes.
- Include documentation updates.

---

# Definition of Done

A task is complete only if:

- [ ] Code is secure.
- [ ] Inputs are sanitized.
- [ ] Outputs are escaped.
- [ ] Nonces added where needed.
- [ ] Capabilities checked.
- [ ] No debug notices.
- [ ] Docs updated.
- [ ] PROJECT_LAYOUT.md updated if needed.
- [ ] Decision logged if architectural.

---

# Security Checklist

Every change must verify:

- No direct file access.
- Proper nonce verification.
- Capability checks.
- Prepared SQL queries.
- REST permission_callback defined.
- No secrets in code.

---

# Performance Checklist

- Scripts loaded only where required.
- No unnecessary queries.
- No heavy operations on init.
- Avoid global state pollution.

---

# Verification Notes

Each feature must include:

- Manual test steps.
- What was tested.
- Expected result.

---

Kiro must follow this workflow strictly.