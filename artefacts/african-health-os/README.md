# African Health OS — Sovereign Digital Public Infrastructure (DPI)

[![Standard](https://img.shields.io/badge/Standard-HL7%20FHIR%20R4-blue.svg)](https://hl7.org/fhir/)
[![Compliance](https://img.shields.io/badge/AU%20CDC-Digital%20Health%20Strategy-gold.svg)](https://africacdc.org/)
[![Security](https://img.shields.io/badge/Data%20Sovereignty-Malabo%20Convention-green.svg)](https://au.int/)

## 1. Vision & Architecture Overview
**African Health OS** is a Sovereign Digital Public Infrastructure (DPI) designed for pan-African healthcare interoperability. It addresses the systemic fragmentation of clinical registries, patient data lock-in by proprietary vendors, and cross-border medical portability across the African continent.

### Core Architectural Principles
1. **Universal Decentralized Patient Identifier (UDPI):** Deterministic, zero-knowledge verifiable health identifier that works offline and cross-border without exposing raw national ID numbers.
2. **HL7 FHIR R4 Native Schema:** Strict compliance with international health interoperability standards, localized for tropical epidemiology and African Union public health reporting.
3. **Cryptographic Tamper-Evident Audit Chain:** Every access, diagnostic update, and prescription event is recorded into an append-only cryptographic hash chain (SHA-256 / Ed25519) ensuring absolute clinical integrity and sovereign data auditability.
4. **Data Sovereignty & Local Residency:** Data stores remain strictly within national sovereign boundaries under domestic data protection authorities (e.g., IPDCP Togo, CNDP, GDPR-equivalent AU frameworks).

---

## 2. Directory Structure
```
african-health-os/
├── README.md                           # This institutional specification
├── schemas/
│   └── patient-identifier-fhir.json    # HL7 FHIR R4 compliant schema for pan-African patient identity
└── core/
    └── audit_trail_hasher.py           # Cryptographic tamper-evident hash chain engine
```

---

## 3. HL7 FHIR Patient Identity Profile
The schema located in `schemas/patient-identifier-fhir.json` enforces:
- Structured demographic and jurisdictional metadata (Country ISO 3166-1 alpha-3).
- Offline-resilient emergency triage flags (Blood group, allergies, chronic conditions).
- Multi-tier consent management compliant with the Malabo Convention on Cybersecurity and Personal Data Protection.

---

## 4. Verification Engine
The Python audit trail verification engine (`core/audit_trail_hasher.py`) provides:
- Append-only hash chain linking previous block hashes with clinical event payloads.
- Tamper detection demonstrating immediate invalidation if any clinical record is modified post-hoc.
- Command-line audit verification for healthcare regulators and hospital ombudsmen.
