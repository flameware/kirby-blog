# Domain Docs

How the engineering skills should consume this repo's domain documentation when exploring the codebase.

This is a **single-context** repo: one `CONTEXT.md` and one `docs/adr/` at the root.

## Before exploring, read this

- **`CONTEXT.md`** at the repo root — the glossary.

For decisions, don't scan `docs/adr/` blind. `AGENTS.md` has an **"어느 ADR을 언제 읽는가"** index that maps areas of the codebase to the ADRs that constrain them; read the lines matching what you're about to touch.

If any of these files don't exist, **proceed silently**. Don't flag their absence; don't suggest creating them upfront. The `/domain-modeling` skill (reached via `/grill-with-docs` and `/improve-codebase-architecture`) creates them lazily when terms or decisions actually get resolved.

## File structure

```
/
├── AGENTS.md          ← 지도와 함정, and the ADR index
├── CLAUDE.md          ← pointer to AGENTS.md
├── CONTEXT.md         ← glossary
├── docs/
│   ├── adr/           ← decisions (why)
│   └── agents/        ← procedures (how)
├── site/
└── assets/
```

Four homes, and only four. `AGENTS.md` holds the map and the traps — things an agent can't work out from the code, or can only work out too late. `docs/adr/` holds *why* a decision was made. `docs/agents/` holds *how* an agent should work here. `CONTEXT.md` holds *what things are called*. If something doesn't fit one of the four, it probably belongs in the code, not in a document.

## Use the glossary's vocabulary

When your output names a domain concept (in an issue title, a refactor proposal, a hypothesis, a test name), use the term as defined in `CONTEXT.md`. Don't drift to synonyms the glossary explicitly avoids.

If the concept you need isn't in the glossary yet, that's a signal — either you're inventing language the project doesn't use (reconsider) or there's a real gap (note it for `/domain-modeling`).

## Flag ADR conflicts

If your output contradicts an existing ADR, surface it explicitly rather than silently overriding:

> _Contradicts ADR-0012 (fluid column width) — but worth reopening because…_

## ADRs are a record, not a spec

An ADR captures a decision **as it was made**. Don't rewrite one to reflect a later change of mind.

- **A reversed decision gets a new ADR.** Add a single `Superseded by ADR-00XX` line at the head of the old one and leave the rest alone.
- **A factual error gets fixed in place.** An identifier that was renamed elsewhere, a path that moved, a command that no longer exists — correct it where it stands, and note what changed so the record stays readable. A stale identifier sends the next reader hunting for something that isn't there. (ADR-0007's `--text-h3` → `--text-subhead` is the worked example.)

The line between the two: if the *decision* still holds and only its vocabulary drifted, fix it. If the decision itself no longer holds, write a new ADR.
