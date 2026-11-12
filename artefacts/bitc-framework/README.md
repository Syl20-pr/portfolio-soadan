# BITC — African AI Trust & Algorithmic Audit Framework

[![License: Apache 2.0](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](LICENSE)
[![Standard: AU AI Continental Strategy](https://img.shields.io/badge/Standard-AU%20AI%20Strategy%202024-gold.svg)](https://au.int)
[![Ethics: UNESCO Recommendation](https://img.shields.io/badge/Ethics-UNESCO%20AI%202021-green.svg)](https://unesco.org)
[![Interop: NIST AI RMF](https://img.shields.io/badge/Interop-NIST%20AI%20RMF%201.0-navy.svg)](https://nist.gov)

**BITC (Blue Intelligence Technologies Corporation)** is an open-source technical trust and algorithmic auditing framework designed specifically for African public institutions, regulators, and research organizations.

It operationalizes the **African Union Continental Artificial Intelligence Strategy (2024)** and the **UNESCO Recommendation on the Ethics of Artificial Intelligence (2021)** by providing verifiable tools to inspect, evaluate, and continuously monitor automated decision systems deployed in public services.

---

## 🏛️ Key Objectives

1. **Digital AI Passport**: A cryptographic, tamper-evident manifest detailing training data provenance, intended operational domain, known limitations, and legal accountability.
2. **Contextual Algorithmic Auditing**: Automated testing for demographic parity, disparate impact, and representational bias across African linguistic and ethnic groups.
3. **Data Sovereignty Compliance**: Verifying local data locality, non-export agreements, and adherence to national data protection legislation and the Malabo Convention.
4. **Continuous Telemetry & Drift Monitoring**: Post-deployment tracking of concept drift, adversarial evasion attempts, and degradation in algorithmic reliability.

---

## 📐 System Architecture

```
┌────────────────────────────────────────────────────────────────────────┐
│                        BITC AI TRUST PLATFORM                          │
├───────────────────────────────────┬────────────────────────────────────┤
│ 1. AI REGISTRY & PASSPORT         │ 2. ALGORITHMIC AUDIT ENGINE        │
│    - Model Card Manifest          │    - Fairness Scanners             │
│    - Data Provenance Hashes       │    - Explainability (SHAP/LIME)    │
│    - Cryptographic Signature      │    - Adversarial Robustness        │
├───────────────────────────────────┼────────────────────────────────────┤
│ 3. CONTINUOUS OBSERVABILITY       │ 4. REGULATORY GATEWAY              │
│    - Data & Concept Drift Logs    │    - AU AI Strategy Compliance     │
│    - Real-Time Latency Metrics    │    - Public Trust Score (0-100)    │
│    - Incident Reporting Webhooks  │    - Machine-Readable Certs        │
└───────────────────────────────────┴────────────────────────────────────┘
```

---

## 📂 Repository Structure

```
bitc-framework/
├── schemas/
│   └── ai-passport-schema.json    # JSON Schema definition for AI Passport
├── core/
│   └── bias_audit_sample.py       # Algorithmic fairness & disparate impact calculator
├── docs/
│   ├── African_Union_Compliance.md
│   └── Model_Card_Specification.md
└── tests/
    └── test_fairness.py
```

---

## 🚀 Quickstart: Generating an AI Passport

```bash
# Clone the repository
git clone https://github.com/sks-governance/bitc-ai-trust-framework.git
cd bitc-ai-trust-framework

# Inspect the AI Passport schema
cat schemas/ai-passport-schema.json | jq .

# Run the fairness evaluation engine
python core/bias_audit_sample.py --dataset test_public_service.csv --target-col decision
```

---

## ⚖️ Legal & Multilateral Alignment

- **African Union**: Aligned with Pillar 2 (Governance & Regulation) of the *Continental AI Strategy*.
- **ECOWAS**: Compatible with the *Supplementary Act on Personal Data Protection*.
- **UNESCO**: Conforms to the *Ethical Impact Assessment (EIA)* methodology.

---

## 👤 Author & Research Contact

**SOADAN Koffi Sylvain**  
Student & Emerging Scholar in Software Engineering (IAI-Togo), Public Law (Univ. of Lomé) & Political Science (Univ. of Kara).  
- Email: [sylvainsoadan3@gmail.com](mailto:sylvainsoadan3@gmail.com)  
- Portfolio: [https://sks-governance.africa](https://sks-governance.africa)
