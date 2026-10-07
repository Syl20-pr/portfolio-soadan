#!/usr/bin/env python3
"""
African Digital Sovereignty Observatory — Composite Scoring Engine
===================================================================
Author: SOADAN Koffi Sylvain (SKS)
Domain: Multilateral Tech Policy, Cyber Diplomacy, AU Governance

Computes weighted composite sovereignty indices for African states across
4 empirical pillars: Legal Readiness, Infrastructure Autonomy, Cyber Defense,
and Local Innovation Agency.
"""

import json
import os
from typing import Dict, Any, List


WEIGHTS = {
    "legal": 0.30,
    "infrastructure": 0.25,
    "cyberDefense": 0.25,
    "innovation": 0.20
}


def compute_composite_score(scores: Dict[str, float]) -> float:
    total = sum(scores[pillar] * WEIGHTS[pillar] for pillar in WEIGHTS)
    return round(total, 2)


def evaluate_tier(score: float) -> str:
    if score >= 85.0:
        return "Tier 1: Sovereign Leader (High Resilience & Legal Readiness)"
    elif score >= 75.0:
        return "Tier 2: Emerging Sovereign (Active Regulatory Convergence)"
    elif score >= 60.0:
        return "Tier 3: Transitioning Sovereign (Infrastructure Gaps)"
    else:
        return "Tier 4: Vulnerable / Dependency Exposed"


def run_assessment(json_path: str):
    print("======================================================================")
    print(" African Digital Sovereignty Observatory — ADSI Benchmark")
    print(" Research Engine by SOADAN Koffi Sylvain (SKS)")
    print("======================================================================")

    if not os.path.exists(json_path):
        print(f"[!] File not found: {json_path}")
        return

    try:
        with open(json_path, "r", encoding="utf-8") as f:
            data = json.load(f)
    except (OSError, json.JSONDecodeError) as exc:
        print(f"[!] Could not read {json_path}: {exc}")
        return

    # Validate the expected shape before indexing into it.
    if "countries" not in data or "methodology" not in data:
        print("[!] Invalid assessment file: 'countries' and 'methodology' keys are required.")
        return
    if not isinstance(data["countries"], list):
        print("[!] Invalid assessment file: 'countries' must be a list.")
        return

    required_country_fields = (
        "iso3", "name", "rec", "scores", "malaboRatified",
        "nationalCert", "sovereignDataCenter",
    )
    missing_pillars = set(WEIGHTS)
    for idx, country in enumerate(data["countries"]):
        if not isinstance(country, dict):
            print(f"[!] Invalid country entry at index {idx}: expected an object.")
            return
        absent = [field for field in required_country_fields if field not in country]
        if absent:
            label = country.get("name", f"index {idx}")
            print(f"[!] Invalid country entry '{label}': missing {', '.join(absent)}.")
            return
        scores = country["scores"]
        if not isinstance(scores, dict) or missing_pillars - set(scores):
            label = country.get("name", f"index {idx}")
            missing = ", ".join(sorted(missing_pillars - set(scores) if isinstance(scores, dict) else missing_pillars))
            print(f"[!] Invalid scores for '{label}': missing {missing}.")
            return

    print(f"\nEvaluating {len(data['countries'])} African Union member states...")
    print(f"Methodology: {data['methodology']}\n")

    ranked_results: List[Dict[str, Any]] = []

    for c in data["countries"]:
        composite = compute_composite_score(c["scores"])
        tier = evaluate_tier(composite)
        ranked_results.append({
            "iso3": c["iso3"],
            "name": c["name"],
            "rec": c["rec"],
            "score": composite,
            "tier": tier,
            "malabo": "RATIFIED" if c["malaboRatified"] else "PENDING",
            "cert": c["nationalCert"],
            "datacenter": c["sovereignDataCenter"]
        })

    # Sort descending by composite score
    ranked_results.sort(key=lambda x: x["score"], reverse=True)

    print(f"{'RANK':<5} | {'COUNTRY':<12} | {'REC':<14} | {'SCORE':<7} | {'MALABO':<9} | {'TIER'}")
    print("-" * 80)
    for idx, r in enumerate(ranked_results, 1):
        print(f"#{idx:<4} | {r['name']:<12} | {r['rec']:<14} | {r['score']:<7.1f} | {r['malabo']:<9} | {r['tier'].split(':')[0]}")

    print("\n--- Detailed Regional Spotlights ---")
    for r in ranked_results:
        print(f"[*] {r['name']} ({r['iso3']}) — Composite Score: {r['score']}/100")
        print(f"    National CERT: {r['cert']}")
        print(f"    Data Sovereignty Facility: {r['datacenter']}")
        print(f"    Strategic Classification: {r['tier']}")
        print()

    print("======================================================================")
    print("[OK] Multilateral policy assessment validated.")


if __name__ == "__main__":
    script_dir = os.path.dirname(os.path.abspath(__file__))
    sample_file = os.path.join(script_dir, "..", "data", "sovereignty_index_sample.json")
    run_assessment(sample_file)
