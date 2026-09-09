# Rimba Laras Workforce Sync

## Purpose

Extends `rimba/laras` with a rerunnable HRDB-to-WFM synchronization pipeline. It synchronizes `Staff` independently from `User`, captures normalized snapshots, detects differences, resolves master data, and applies WFM workforce transitions.

## Ownership

- `rimba/laras` owns source ingestion, sync runs, snapshots, differences, normalization, and classification.
- `rimba/orang` owns `Staff` identity only.
- `rimba/pihak` owns organization master data.
- `rimba/jawat` owns job positions.
- `rimba/waktu` owns shifts.
- `rimba/wfm` owns `WorkforceAssignment`, `WorkforceEvent`, and lifecycle transitions.

## Pipeline

1. Fetch source through the existing Laras `rimba:fetch` command, unless `--no-fetch` is used.
2. Read `ApiData` records for the selected `ApiConfig`.
3. Normalize the HRDB payload and remove credentials and irrelevant fields.
4. Compare the normalized checksum with the previous successful snapshot.
5. Synchronize Staff and referenced master data.
6. Detect changes against the previous snapshot.
7. Classify the combined changes as a WFM event.
8. Apply the event through `WorkforceAssignmentService`.
9. Store the new immutable snapshot and detailed change records.
10. Complete the run with auditable counters.

## Idempotency

- Source identity is `uuid` by default.
- An unchanged checksum is skipped.
- Snapshots are unique by sync run and source UUID.
- WFM event references use `hrdb:{source_uuid}:{checksum}`.
- Reprocessing the same source state does not create another WFM event.

## Staff and User

This pipeline never creates, updates, or links a User. Staff synchronization is independent.
